document.addEventListener('alpine:init', () => {
    Alpine.data('scannerApp', () => ({
        isMobile: false,
        workerReady: false,
        hasCamera: false,
        cameraReady: false,
        cameraError: '',
        scanning: false,
        scanMode: 'manual',
        mode: 'usd-to-bs',
        result: null,
        history: [],
        engine: '',
        _worker: null,
        _stream: null,
        _rateCache: null,
        _autoTimer: null,

        init() {
            this.isMobile = this.checkMobile()
            if (!this.isMobile) return
            this.loadHistory()
            this.loadMode()
            this.loadScanMode()
            this.initWorker()
            this.startCamera()
        },

        destroy() {
            this.stopAutoScan()
            this.stopScanner()
        },

        checkMobile() {
            return window.innerWidth < 1024
                && ('ontouchstart' in window || navigator.maxTouchPoints > 0)
        },

        get canScan() {
            return this.workerReady && this.cameraReady && !this.scanning
        },

        loadHistory() {
            try {
                const raw = localStorage.getItem('scanner_history')
                this.history = raw ? JSON.parse(raw) : []
            } catch (e) { this.history = [] }
        },

        saveHistory() {
            try {
                localStorage.setItem('scanner_history', JSON.stringify(this.history.slice(0, 20)))
            } catch (e) { /* localStorage lleno */ }
        },

        loadMode() {
            try {
                const saved = localStorage.getItem('scanner_mode')
                if (saved === 'bs-to-usd' || saved === 'usd-to-bs') this.mode = saved
            } catch (e) { /* ignorar */ }
        },

        setMode(m) {
            this.mode = m
            try { localStorage.setItem('scanner_mode', m) } catch (e) { /* ignorar */ }
        },

        loadScanMode() {
            try {
                const saved = localStorage.getItem('scanner_scan_mode')
                if (saved === 'auto') this.scanMode = 'auto'
            } catch (e) { /* ignorar */ }
        },

        toggleScanMode() {
            this.scanMode = this.scanMode === 'manual' ? 'auto' : 'manual'
            try { localStorage.setItem('scanner_scan_mode', this.scanMode) } catch (e) { /* ignorar */ }
            if (this.scanMode === 'auto') this.startAutoScan()
            else this.stopAutoScan()
        },

        startAutoScan() {
            this.stopAutoScan()
            const loop = async () => {
                if (this.scanMode !== 'auto') return
                await this.scanNow()
                this._autoTimer = setTimeout(loop, 3000)
            }
            this._autoTimer = setTimeout(loop, 500)
        },

        stopAutoScan() {
            if (this._autoTimer) {
                clearTimeout(this._autoTimer)
                this._autoTimer = null
            }
        },

        get inputSymbol() {
            return this.mode === 'usd-to-bs' ? '$' : 'Bs'
        },

        get outputSymbol() {
            return this.mode === 'usd-to-bs' ? 'Bs' : '$'
        },

        async initWorker() {
            try {
                this._worker = await Tesseract.createWorker('eng')
                await this._worker.setParameters({
                    tessedit_char_whitelist: '0123456789.,$',
                    tessedit_pageseg_mode: '7',
                })
                this.workerReady = true
            } catch (e) {
                /* worker falló, Gemini igual funciona */
            }
        },

        async startCamera() {
            let stream = null
            let lastError = null

            for (const constraints of [
                { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } },
                { facingMode: 'environment' },
                true,
            ]) {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: constraints })
                    break
                } catch (e) { lastError = e }
            }

            if (!stream) {
                const name = lastError?.name || ''
                if (name === 'NotAllowedError') this.cameraError = 'Permiso de cámara denegado.'
                else if (name === 'NotFoundError') this.cameraError = 'No se detectó cámara en este dispositivo.'
                else if (name === 'NotReadableError') this.cameraError = 'Cámara ocupada por otra app.'
                else if (name === 'OverconstrainedError') this.cameraError = 'Cámara no soporta el formato requerido.'
                else this.cameraError = 'Error de cámara (' + name + ')'
                return
            }

            this._stream = stream
            const video = this.$refs.video
            video.srcObject = stream

            await new Promise((resolve) => {
                const timeout = setTimeout(resolve, 3000)
                video.addEventListener('loadeddata', () => {
                    clearTimeout(timeout)
                    resolve()
                }, { once: true })
                if (video.readyState >= 2) { clearTimeout(timeout); resolve() }
                video.play().catch(() => {})
            })

            this.hasCamera = true

            const waitForFrames = () => {
                if (video.videoWidth > 0) {
                    this.cameraReady = true
                    if (this.scanMode === 'auto') this.startAutoScan()
                    return
                }
                setTimeout(waitForFrames, 100)
            }
            waitForFrames()
        },

        async scanNow() {
            if (!this.canScan) return

            const video = this.$refs.video
            const scanZone = this.$refs.scanZone

            if (!video.videoWidth || !scanZone) return

            this.scanning = true
            const safety = setTimeout(() => { this.scanning = false }, 15000)

            try {
                const vw = video.videoWidth, vh = video.videoHeight
                const container = video.parentElement
                const crect = container.getBoundingClientRect()
                const srect = scanZone.getBoundingClientRect()

                const cw = crect.width, ch = crect.height
                const va = vw / vh, ca = cw / ch

                let dw, dh, ox, oy
                if (va > ca) { dh = ch; dw = ch * va; ox = (cw - dw) / 2; oy = 0 }
                else { dw = cw; dh = cw / va; ox = 0; oy = (ch - dh) / 2 }

                const rx = srect.left - crect.left, ry = srect.top - crect.top
                const rw = srect.width, rh = srect.height

                let cx = Math.max(0, Math.round((rx - ox) * vw / dw))
                let cy = Math.max(0, Math.round((ry - oy) * vh / dh))
                let cw2 = Math.min(vw - cx, Math.round(rw * vw / dw))
                let ch2 = Math.min(vh - cy, Math.round(rh * vh / dh))

                if (cw2 < 10 || ch2 < 10) return

                const img = document.createElement('canvas')
                img.width = cw2; img.height = ch2
                const ictx = img.getContext('2d')
                ictx.drawImage(video, cx, cy, cw2, ch2, 0, 0, cw2, ch2)

                let value = null

                // 1. Try Gemini API
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || window.csrfToken
                    const b64 = img.toDataURL('image/png')
                    const res = await fetch('/admin/scanner/ocr', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ image: b64 }),
                    })
                    const data = await res.json()
                    if (data.success) {
                        value = data.value
                        this.engine = 'gemini'
                    }
                } catch (e) { /* Gemini falló, usar Tesseract */ }

                // 2. Fallback: Tesseract
                if (value === null && this._worker) {
                    try {
                        const imageData = ictx.getImageData(0, 0, cw2, ch2)
                        const d = imageData.data
                        let min = 255, max = 0
                        for (let i = 0; i < d.length; i += 4) {
                            const g = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2]
                            d[i] = d[i + 1] = d[i + 2] = g
                            if (g < min) min = g
                            if (g > max) max = g
                        }
                        if (max - min > 15) {
                            for (let i = 0; i < d.length; i += 4) {
                                d[i] = d[i + 1] = d[i + 2] = (d[i] - min) / (max - min) * 255
                            }
                        }
                        ictx.putImageData(imageData, 0, 0)

                        const { data: { text } } = await this._worker.recognize(img)
                        const cleaned = text.replace(/[^0-9.,]/g, '').replace(',', '.')
                        const match = cleaned.match(/(\d+\.?\d*)/)
                        if (match) {
                            const v = parseFloat(match[1])
                            if (v > 0) { value = v; this.engine = 'tesseract' }
                        }
                    } catch (e) { /* OCR falló totalmente */ }
                }

                if (value !== null && value > 0) {
                    const rate = await this.getRate()
                    this.convertAndShow(value, rate)
                }
            } catch (e) { /* error inesperado en scan */ }

            clearTimeout(safety)
            this.scanning = false
        },

        convertAndShow(value, rate) {
            const isUsdToBs = this.mode === 'usd-to-bs'
            const converted = isUsdToBs ? value * rate : value / rate

            const fmt = (n, curr) => {
                return curr + ' ' + n.toLocaleString(curr === 'Bs' ? 'es-VE' : 'en-US', {
                    minimumFractionDigits: 2, maximumFractionDigits: 2,
                })
            }

            this.result = {
                value, mode: this.mode,
                original: fmt(value, this.inputSymbol),
                converted: fmt(converted, this.outputSymbol),
                rate, engine: this.engine,
            }

            const historyItem = { ...this.result, time: new Date().toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit' }) }
            this.history.unshift(historyItem)
            this.saveHistory()

            if (navigator.vibrate) navigator.vibrate(50)
        },

        async getRate() {
            if (this._rateCache) return this._rateCache

            try {
                const controller = new AbortController()
                const timeout = setTimeout(() => controller.abort(), 5000)
                const res = await fetch('/admin/exchange-rate/current', { signal: controller.signal })
                clearTimeout(timeout)
                const data = await res.json()
                if (data.success) {
                    localStorage.setItem('scanner_rate', JSON.stringify({ rate: data.rate, updated_at: data.updated_at, time: Date.now() }))
                    this._rateCache = data.rate
                    return data.rate
                }
            } catch (e) { /* offline */ }

            try {
                const cached = JSON.parse(localStorage.getItem('scanner_rate'))
                if (cached?.rate) { this._rateCache = cached.rate; return cached.rate }
            } catch (e) { /* corrupto */ }

            this._rateCache = window.initialRate || 134
            return this._rateCache
        },

        clearHistory() {
            this.history = []
            localStorage.removeItem('scanner_history')
        },

        stopScanner() {
            if (this._stream) { this._stream.getTracks().forEach(t => t.stop()); this._stream = null }
            if (this._worker) { try { this._worker.terminate() } catch (e) { /* ya terminó */ }; this._worker = null }
        },
    }))
})
