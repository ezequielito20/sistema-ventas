document.addEventListener('alpine:init', () => {
    Alpine.data('scannerApp', () => ({
        isMobile: false,
        workerReady: false,
        hasCamera: false,
        cameraError: '',
        scanning: false,
        mode: 'usd-to-bs',
        zoom: 1,
        result: null,
        history: [],
        _worker: null,
        _stream: null,
        _rateCache: null,
        _pinchStart: null,

        init() {
            this.isMobile = this.checkMobile()
            if (!this.isMobile) return
            this.loadHistory()
            this.loadMode()
            this.initWorker()
        },

        destroy() {
            this.stopScanner()
        },

        checkMobile() {
            return window.innerWidth < 1024
                && ('ontouchstart' in window || navigator.maxTouchPoints > 0)
        },

        loadHistory() {
            try {
                const raw = localStorage.getItem('scanner_history')
                this.history = raw ? JSON.parse(raw) : []
            } catch (e) {
                this.history = []
            }
        },

        saveHistory() {
            try {
                localStorage.setItem('scanner_history', JSON.stringify(this.history.slice(0, 20)))
            } catch (e) { /* localStorage lleno */ }
        },

        loadMode() {
            try {
                const saved = localStorage.getItem('scanner_mode')
                if (saved === 'bs-to-usd' || saved === 'usd-to-bs') {
                    this.mode = saved
                }
            } catch (e) { /* ignorar */ }
        },

        setMode(m) {
            this.mode = m
            try { localStorage.setItem('scanner_mode', m) } catch (e) { /* ignorar */ }
        },

        get inputSymbol() {
            return this.mode === 'usd-to-bs' ? '$' : 'Bs'
        },

        get outputSymbol() {
            return this.mode === 'usd-to-bs' ? 'Bs' : '$'
        },

        get zoomPercent() {
            return Math.round(this.zoom * 100) + '%'
        },

        setZoom(val) {
            this.zoom = Math.max(1, Math.min(3, parseFloat(val) || 1))
        },

        handleTouchStart(e) {
            if (e.touches.length === 2) {
                this._pinchStart = {
                    dist: Math.hypot(
                        e.touches[0].clientX - e.touches[1].clientX,
                        e.touches[0].clientY - e.touches[1].clientY,
                    ),
                    zoom: this.zoom,
                }
            }
        },

        handleTouchMove(e) {
            if (e.touches.length === 2 && this._pinchStart) {
                e.preventDefault()
                const dist = Math.hypot(
                    e.touches[0].clientX - e.touches[1].clientX,
                    e.touches[0].clientY - e.touches[1].clientY,
                )
                const scale = dist / this._pinchStart.dist
                this.zoom = Math.max(1, Math.min(3, this._pinchStart.zoom * scale))
            }
        },

        handleTouchEnd() {
            this._pinchStart = null
        },

        async initWorker() {
            try {
                this._worker = await Tesseract.createWorker('eng')
                await this._worker.setParameters({
                    tessedit_char_whitelist: '0123456789.$',
                })
                this.workerReady = true
                this.startCamera()
            } catch (e) {
                this.cameraError = 'Error al cargar el motor OCR.'
            }
        },

        async startCamera() {
            let stream = null
            let lastError = null

            for (const constraints of [{ facingMode: 'environment' }, true]) {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: constraints })
                    break
                } catch (e) {
                    lastError = e
                }
            }

            if (!stream) {
                const name = lastError?.name || ''
                if (name === 'NotAllowedError') {
                    this.cameraError = 'Permiso de cámara denegado.'
                } else if (name === 'NotFoundError') {
                    this.cameraError = 'No se detectó cámara en este dispositivo.'
                } else if (name === 'NotReadableError') {
                    this.cameraError = 'Cámara ocupada por otra app.'
                } else if (name === 'OverconstrainedError') {
                    this.cameraError = 'Cámara no soporta el formato requerido.'
                } else {
                    this.cameraError = 'Error de cámara (' + name + ')'
                }
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

                if (video.readyState >= 2) {
                    clearTimeout(timeout)
                    resolve()
                }

                video.play().catch(() => {})
            })

            this.hasCamera = true
        },

        async scanNow() {
            if (!this.workerReady || !this.hasCamera || this.scanning) return

            const video = this.$refs.video
            const canvas = this.$refs.canvas

            if (!video.videoWidth) {
                video.play().catch(() => {})
                return
            }

            this.scanning = true

            const vw = video.videoWidth
            const vh = video.videoHeight
            const zoom = this.zoom

            canvas.width = vw
            canvas.height = vh

            if (zoom > 1) {
                const cropW = vw / zoom
                const cropH = vh / zoom
                const cx = (vw - cropW) / 2
                const cy = (vh - cropH) / 2
                canvas.getContext('2d').drawImage(video, cx, cy, cropW, cropH, 0, 0, vw, vh)
            } else {
                canvas.getContext('2d').drawImage(video, 0, 0)
            }

            try {
                const { data: { text } } = await this._worker.recognize(canvas)
                const cleaned = text.replace(/[^0-9.]/g, '')
                const match = cleaned.match(/(\d+\.?\d*)/)

                if (match) {
                    const value = parseFloat(match[1])
                    if (value > 0) {
                        const rate = await this.getRate()
                        this.convertAndShow(value, rate)
                    }
                }
            } catch (e) { /* OCR falló */ }

            this.scanning = false
        },

        convertAndShow(value, rate) {
            const isUsdToBs = this.mode === 'usd-to-bs'
            const converted = isUsdToBs ? value * rate : value / rate

            const fmt = (n, curr) => {
                const locale = curr === 'Bs' ? 'es-VE' : 'en-US'
                return curr + ' ' + n.toLocaleString(locale, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                })
            }

            this.result = {
                value,
                mode: this.mode,
                original: fmt(value, this.inputSymbol),
                converted: fmt(converted, this.outputSymbol),
                rate: rate,
            }

            const historyItem = {
                ...this.result,
                time: new Date().toLocaleTimeString('es-VE', { hour: '2-digit', minute: '2-digit' }),
            }
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
                    localStorage.setItem('scanner_rate', JSON.stringify({
                        rate: data.rate,
                        updated_at: data.updated_at,
                        time: Date.now(),
                    }))
                    this._rateCache = data.rate
                    return data.rate
                }
            } catch (e) { /* offline o timeout */ }

            try {
                const cached = JSON.parse(localStorage.getItem('scanner_rate'))
                if (cached && cached.rate) {
                    this._rateCache = cached.rate
                    return cached.rate
                }
            } catch (e) { /* corrupto */ }

            this._rateCache = window.initialRate || 134
            return this._rateCache
        },

        clearHistory() {
            this.history = []
            localStorage.removeItem('scanner_history')
        },

        stopScanner() {
            if (this._stream) {
                this._stream.getTracks().forEach(t => t.stop())
                this._stream = null
            }
            if (this._worker) {
                try { this._worker.terminate() } catch (e) { /* ya terminó */ }
                this._worker = null
            }
        },
    }))
})
