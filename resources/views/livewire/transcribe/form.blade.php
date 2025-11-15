<div x-data="voiceRecorder()" x-init="init()" class="bg-zinc-50 dark:bg-zinc-900 h-screen">
    <!-- Processing Modal -->
    <flux:modal name="processing-modal" variant="default" :dismissible="false" :closable="false">
        <div class="flex flex-col items-center gap-6 p-6">
            <!-- Animated spinner -->
            <div class="relative w-20 h-20">
                <div class="absolute inset-0 border-4 border-zinc-200 dark:border-zinc-700 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-primary-500 rounded-full border-t-transparent animate-spin"></div>
            </div>
            
            <div class="text-center">
                <flux:heading size="lg" class="mb-2">Processing Your Recording</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-400" x-text="processingMessage"></flux:text>
            </div>
            
            <!-- Progress indicator -->
            <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5 overflow-hidden">
                <div class="h-full bg-primary-500 rounded-full animate-pulse transition-all duration-500" style="width: 70%"></div>
            </div>
        </div>
    </flux:modal>

    <div class="grid grid-cols-1 md:grid-cols-2">
        <!-- Left Column: Cassette & Controls -->
        <div class="self-start  p-6">
            <div class="flex flex-col h-full">
                <!-- Cassette Tape -->
                <div class="" :class="{ 'playing': status === 'recording' || status === 'transcribing' }">
                    <x-cassette-player />
                </div>

                <!-- Recording Controls -->
                <div class="mt-6 flex flex-col items-center gap-4 w-full">
                    <div class="w-full flex gap-2" x-show="status === 'idle' || status === 'recording'">
                        <flux:button
                            type="button"
                            @click="startRecording"
                            variant="primary"
                            icon-trailing="microphone"
                            class="flex-1"
                            ::disabled="status === 'recording'"
                            size="sm"
                        >
                            Start Recording
                        </flux:button>
                        
                        <flux:button
                            type="button"
                            @click="stopRecording"
                            variant="danger"
                            icon-trailing="stop-circle"
                            ::class="status === 'recording' ? 'flex-1 animate-pulse' : 'flex-1'"
                            ::disabled="status === 'idle'"
                            size="sm"
                        >
                            Stop Recording
                        </flux:button>
                    </div>

                    <template x-if="status === 'completed' && audioUrl">
                        <div class="w-full space-y-3">
                            <audio controls class="w-full" :src="audioUrl"></audio>
                            <div class="flex gap-2">
                                <flux:button type="button" size="sm" @click="clearRecording" variant="ghost" class="flex-1">
                                    New Recording
                                </flux:button>
                                <flux:button type="button" size="sm" @click="saveVoiceNote" ::disabled="(!liveTranscript && !$wire.recordedAudio) || isSaving" variant="primary" class="flex-1">
                                    <span x-show="!isSaving">Save Voice Note</span>
                                    <span x-show="isSaving" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Saving...
                                    </span>
                                </flux:button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Right Column: Transcripts -->
        <div class="flex flex-col h-screen">
            <div class="overflow-y-auto min-h-28 pb-72 md:pb-28">
                <!-- Live Transcript -->
                <div class="flex-1 flex flex-col p-6 min-h-0">
                    <div class="flex items-center justify-between mb-3 flex-shrink-0">
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Live Transcript
                        </h3>
                        <div x-show="status === 'recording'" class="text-2xl font-mono font-bold text-zinc-700 dark:text-zinc-300" x-text="formatTime(recordingTime)"></div>
                    </div>
                    <div class="flex-1 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-y-auto min-h-0">
                        <p x-show="!liveTranscript" class="text-zinc-400 italic">Start recording to see live transcription...</p>
                        <p x-show="liveTranscript" class="text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap" x-text="liveTranscript"></p>
                    </div>
                </div>

                <!-- Formatted Notes -->
                <div x-show="formattedNotes" class="flex-1 flex flex-col p-6 border-t border-zinc-200 dark:border-zinc-700 min-h-0">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-3 flex items-center gap-2 flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                        </svg>
                        AI-Formatted Notes
                    </h3>
                    <div class="flex-1 prose dark:prose-invert max-w-none p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg overflow-y-auto min-h-0">
                        <div x-html="formatMarkdown(formattedNotes)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function voiceRecorder() {
            return {
                status: 'idle',
                isRecording: false,
                mediaRecorder: null, // continuous full-session recorder
                txRecorder: null, // rotating chunk recorder for transcription
                stream: null,
                audioChunks: [],
                recordingTime: 0,
                timerInterval: null,
                chunkInterval: null,
                chunkCounter: 0,
                liveTranscript: '',
                formattedNotes: '',
                audioUrl: null,
                processingMessage: 'Please wait while we process your recording...',
                isSaving: false,
                isSaving: false,

                init() {
                    // Listen for wire updates
                    Livewire.on('transcription-chunk', (data) => {
                        this.liveTranscript += data.text + ' ';
                    });

                    // Watch status changes to control modal
                    this.$watch('status', (value) => {
                        if (value === 'processing' || value === 'formatting') {
                            this.$flux.modal('processing-modal').show();
                        } else if (value === 'completed' || value === 'idle') {
                            this.$flux.modal('processing-modal').close();
                        }
                    });
                },

                async toggleRecording() {
                    if (this.status === 'idle') {
                        await this.startRecording();
                    } else if (this.status === 'recording') {
                        await this.stopRecording();
                    }
                },

                async startRecording() {
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        this.audioChunks = [];
                        this.liveTranscript = '';
                        this.formattedNotes = '';
                        this.chunkCounter = 0;

                        this.status = 'recording';
                        this.isRecording = true;
                        this.startTimer();

                        // Start continuous recorder for full audio
                        this.startFullRecorder();

                        // Start first rotating chunk for transcription
                        this.startNewChunk();

                        // Rotate transcription chunk every 3 seconds
                        this.chunkInterval = setInterval(() => {
                            if (this.isRecording) {
                                this.rotateChunk();
                            }
                        }, 3000);

                    } catch (error) {
                        console.error('Error accessing microphone:', error);
                        alert('Could not access microphone. Please check permissions.');
                    }
                },

                startFullRecorder() {
                    let options = undefined;
                    try {
                        const preferred = 'audio/webm;codecs=opus';
                        const fallback = 'audio/webm';
                        if (window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(preferred)) {
                            options = { mimeType: preferred };
                        } else if (window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(fallback)) {
                            options = { mimeType: fallback };
                        } else {
                            options = undefined; // Let browser pick default
                        }

                        this.mediaRecorder = options ? new MediaRecorder(this.stream, options) : new MediaRecorder(this.stream);
                    } catch (e) {
                        console.error('MediaRecorder init failed:', e);
                        alert('Recording format not supported by this browser. Try Chrome/Edge.');
                        this.isRecording = false;
                        this.status = 'idle';
                        return;
                    }

                    this.mediaRecorder.addEventListener('dataavailable', (event) => {
                        if (event.data && event.data.size > 0) {
                            this.audioChunks.push(event.data);
                        }
                    });

                    try {
                        this.mediaRecorder.start();
                    } catch (e) {
                        console.error('MediaRecorder start failed:', e);
                        alert('Failed to start recording. Please try again.');
                        this.isRecording = false;
                        this.status = 'idle';
                    }
                },

                startNewChunk() {
                    let options = undefined;
                    try {
                        const preferred = 'audio/webm;codecs=opus';
                        const fallback = 'audio/webm';
                        if (window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(preferred)) {
                            options = { mimeType: preferred };
                        } else if (window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(fallback)) {
                            options = { mimeType: fallback };
                        } else {
                            options = undefined;
                        }

                        this.txRecorder = options ? new MediaRecorder(this.stream, options) : new MediaRecorder(this.stream);
                    } catch (e) {
                        console.error('MediaRecorder init failed:', e);
                        this.isRecording = false;
                        this.status = 'idle';
                        return;
                    }

                    const currentChunkData = [];

                    this.txRecorder.addEventListener('dataavailable', (event) => {
                        if (event.data && event.data.size > 0) {
                            currentChunkData.push(event.data);
                        }
                    });

                    this.txRecorder.addEventListener('stop', async () => {
                        if (currentChunkData.length > 0) {
                            const chunkBlob = new Blob(currentChunkData, { type: 'audio/webm' });
                            await this.transcribeChunk(chunkBlob, this.chunkCounter++);
                        }
                    });

                    try {
                        this.txRecorder.start();
                    } catch (e) {
                        console.error('MediaRecorder start failed:', e);
                        this.isRecording = false;
                        this.status = 'idle';
                    }
                },

                rotateChunk() {
                    if (this.txRecorder && this.txRecorder.state === 'recording') {
                        this.txRecorder.stop();
                        setTimeout(() => this.startNewChunk(), 100);
                    }
                },

                async stopRecording() {
                    if (!this.isRecording) return;

                    this.isRecording = false;
                    this.stopTimer();
                    
                    // Show processing modal
                    this.status = 'processing';
                    this.processingMessage = 'Stopping recording and processing audio...';

                    // Clear interval
                    if (this.chunkInterval) {
                        clearInterval(this.chunkInterval);
                        this.chunkInterval = null;
                    }

                    // Stop transcription recorder and wait
                    if (this.txRecorder && this.txRecorder.state === 'recording') {
                        await new Promise((resolve) => {
                            this.txRecorder.addEventListener('stop', resolve, { once: true });
                            this.txRecorder.stop();
                        });
                    }

                    // Stop continuous recorder and wait for final data
                    if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
                        await new Promise((resolve) => {
                            this.mediaRecorder.addEventListener('stop', resolve, { once: true });
                            this.mediaRecorder.stop();
                        });
                    }

                    // Stop stream
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }

                    // Small wait for any pending operations
                    await new Promise(resolve => setTimeout(resolve, 300));

                    // Create complete audio from all chunks
                    console.log(`Creating audio blob from ${this.audioChunks.length} chunks`);
                    console.log('Chunk sizes:', this.audioChunks.map(c => c.size));
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                    console.log(`Final audio blob size: ${audioBlob.size} bytes`);
                    this.audioUrl = URL.createObjectURL(audioBlob);
                    
                    this.status = 'formatting';
                    this.processingMessage = 'Formatting your transcript with AI...';
                    this.formatTranscriptNow();
                },

                async formatTranscriptNow() {
                    try {
                        const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                        const reader = new FileReader();
                        const wire = this.$wire;

                        reader.onloadend = async () => {
                            try {
                                await wire.set('recordedAudio', reader.result);

                                if (this.liveTranscript.trim()) {
                                    const formatted = await wire.call('formatTranscript', this.liveTranscript.trim(), this.recordingTime);
                                    if (formatted) {
                                        this.formattedNotes = formatted;
                                    }
                                }

                                this.status = 'completed';
                            } catch (error) {
                                console.error('Formatting error:', error);
                                this.status = 'completed';
                            }
                        };

                        reader.readAsDataURL(audioBlob);
                    } catch (error) {
                        console.error('Format error:', error);
                        this.status = 'completed';
                    }
                },

                async transcribeChunk(audioBlob, chunkIndex) {
                    const formData = new FormData();
                    const file = new File([audioBlob], `chunk_${chunkIndex}.webm`, { type: 'audio/webm' });
                    formData.append('audio_chunk', file);

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        
                        const response = await fetch('/voice-note/transcribe-chunk', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        let data = null;
                        try {
                            data = await response.json();
                        } catch (e) {
                            // ignore parse errors
                        }

                        if (!response.ok) {
                            console.error('Transcription failed:', (data && data.error) ? data.error : `HTTP ${response.status}`);
                            return;
                        }

                        if (data && data.text) {
                            this.liveTranscript += data.text + ' ';
                        }
                    } catch (error) {
                        console.error('Transcription error:', error);
                    }
                },

                clearRecording() {
                    this.status = 'idle';
                    this.audioChunks = [];
                    this.recordingTime = 0;
                    this.liveTranscript = '';
                    this.formattedNotes = '';
                    this.audioUrl = null;
                    this.$wire.call('clearRecording');
                },

                async saveVoiceNote() {
                    this.isSaving = true;
                    try {
                        // Sync Alpine state to Livewire before saving
                        await this.$wire.set('liveTranscript', this.liveTranscript);
                        await this.$wire.set('formattedNotes', this.formattedNotes);
                        
                        // Now save with the synced data
                        await this.$wire.call('saveVoiceNote');
                    } catch (error) {
                        console.error('Save error:', error);
                        alert('Failed to save voice note. Please try again.');
                    } finally {
                        this.isSaving = false;
                    }
                },

                startTimer() {
                    this.recordingTime = 0;
                    this.timerInterval = setInterval(() => {
                        this.recordingTime++;
                    }, 1000);
                },

                stopTimer() {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                        this.timerInterval = null;
                    }
                },

                formatTime(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                },

                formatMarkdown(text) {
                    return text
                        .replace(/\n/g, '<br>')
                        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.+?)\*/g, '<em>$1</em>')
                        .replace(/^- (.+)/gm, '<li>$1</li>')
                        .replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
                }
            }
        }
    </script>
</div>
