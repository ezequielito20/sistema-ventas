document.addEventListener('alpine:init', () => {
    Alpine.data('scannerApp', () => ({
        isMobile: false,
        workerReady: false,
        hasCamera: false,
        cameraError: '',
        mode: 'usd-to-bs',
        result: null,
        history: [],
        _worker: null,
        _stream: null,
        _scanTimer: null,
        _rateCache: null,

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

        get modeLabel() {
            return this.mode === 'usd-to-bs' ? '$ → Bs' : 'Bs → $'
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
                    tessedit_char_whitelist: '0123456789.$',
                })
                this.workerReady = true
                this.startCamera()
            } catch (e) {
                this.cameraError = 'Error al cargar el motor OCR. Verificá tu conexión a internet.'
            }
        },

        async startCamera() {
            try {
                this._stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment',
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                    },
                })
                this.$refs.video.srcObject = this._stream
                await this.$refs.video.play()
                this.hasCamera = true
                this.startScanLoop()
            } catch (e) {
                if (e.name === 'NotAllowedError') {
                    this.cameraError = 'Permiso de cámara denegado. Habilitalo en la configuración del navegador.'
                } else if (e.name === 'NotFoundError') {
                    this.cameraError = 'No se detectó una cámara en este dispositivo.'
                } else {
                    this.cameraError = 'No se pudo acceder a la cámara.'
                }
            }
        },

        startScanLoop() {
            const scan = async () => {
                if (!this.workerReady || !this.hasCamera) return

                const canvas = this.$refs.canvas
                const video = this.$refs.video

                if (!video.videoWidth) {
                    this._scanTimer = setTimeout(scan, 500)
                    return
                }

                canvas.width = video.videoWidth
                canvas.height = video.videoHeight
                canvas.getContext('2d').drawImage(video, 0, 0)

                try {
                    const { data: { text } } = await this._worker.recognize(canvas)
                    const cleaned = text.replace(/[^0-9.]/g, '')
                    const match = cleaned.match(/(\d+\.?\d*)/)

                    if (match) {
                        const value = parseFloat(match[1])
                        if (value > 0 && (!this.result || Math.abs(value - this.result.value) > 0.01)) {
                            const rate = await this.getRate()
                            this.convertAndShow(value, rate)
                        }
                    }
                } catch (e) { /* frame de OCR falló, siguiente */ }

                this._scanTimer = setTimeout(scan, 2500)
            }
            this._scanTimer = setTimeout(scan, 1000)
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
            if (this._scanTimer) {
                clearTimeout(this._scanTimer)
                this._scanTimer = null
            }
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
