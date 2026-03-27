<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} | Видеовстреча</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --brb-red: #E52716;
            --bg-dark: #0f0f1a;
            --bg-darker: #0a0a12;
            --bg-card: #1a1a2e;
            --bg-hover: #252542;
            --text-primary: #ffffff;
            --text-secondary: #8888a0;
            --border: rgba(255,255,255,0.08);
            --success: #22c55e;
            --warning: #f59e0b;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
        }

        /* Layout */
        .room-container {
            display: grid;
            grid-template-columns: 1fr 360px;
            height: 100vh;
        }

        /* Main Video Area */
        .video-section {
            display: flex;
            flex-direction: column;
            background: var(--bg-darker);
            position: relative;
        }

        .video-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
        }
        .meeting-info h1 {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .meeting-info span {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .timer {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.05);
            padding: 10px 20px;
            border-radius: 30px;
        }
        .timer-dot {
            width: 10px;
            height: 10px;
            background: var(--brb-red);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
        .timer-value {
            font-size: 1.1rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .video-grid {
            flex: 1;
            display: grid;
            gap: 16px;
            padding: 24px;
            grid-template-columns: 1fr;
            align-content: center;
        }
        .video-grid.multiple {
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        }

        .video-tile {
            position: relative;
            background: var(--bg-card);
            border-radius: 20px;
            overflow: hidden;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .video-tile video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .video-tile.local {
            position: absolute;
            bottom: 120px;
            right: 24px;
            width: 280px;
            height: 158px;
            z-index: 50;
            border: 3px solid var(--bg-card);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .video-label {
            position: absolute;
            bottom: 16px;
            left: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 10px;
        }
        .video-label span {
            font-size: 0.9rem;
            font-weight: 500;
        }
        .video-label .muted-icon {
            color: var(--brb-red);
        }

        .video-placeholder {
            text-align: center;
            color: var(--text-secondary);
        }
        .video-placeholder .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brb-red), #ff6b5b);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 2.5rem;
            color: white;
        }
        .video-placeholder p {
            font-size: 1rem;
        }

        /* Controls */
        .controls-bar {
            display: flex;
            justify-content: center;
            gap: 16px;
            padding: 20px;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
        }
        .control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            background: var(--bg-hover);
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .control-btn:hover {
            background: rgba(255,255,255,0.15);
            transform: scale(1.05);
        }
        .control-btn.active {
            background: var(--brb-red);
        }
        .control-btn.end-call {
            background: var(--brb-red);
            width: 70px;
            height: 70px;
            font-size: 1.5rem;
        }
        .control-btn.end-call:hover {
            background: #c41e0a;
        }

        /* Sidebar */
        .sidebar {
            background: var(--bg-card);
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-header h2 {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-header h2 i {
            color: var(--brb-red);
        }
        .participants-list {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        .participant-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: var(--bg-hover);
            border-radius: 14px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }
        .participant-card:hover {
            background: rgba(255,255,255,0.08);
        }
        .participant-card .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brb-red), #ff6b5b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        .participant-card .info {
            flex: 1;
        }
        .participant-card .name {
            font-weight: 500;
            font-size: 0.95rem;
        }
        .participant-card .status {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .participant-card .status.online {
            color: var(--success);
        }
        .participant-card .status.waiting {
            color: var(--warning);
        }
        .participant-card .icons {
            display: flex;
            gap: 8px;
            color: var(--text-secondary);
        }
        .participant-card .icons i.off {
            color: var(--brb-red);
        }

        /* Waiting Request Card */
        .waiting-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .waiting-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .waiting-card-header .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--warning);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        .waiting-card-header .info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .waiting-card-header .info p {
            font-size: 0.8rem;
            color: var(--warning);
        }
        .waiting-card-actions {
            display: flex;
            gap: 10px;
        }
        .waiting-card-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-accept {
            background: var(--success);
            color: white;
        }
        .btn-accept:hover {
            background: #16a34a;
        }
        .btn-reject {
            background: rgba(255,255,255,0.1);
            color: var(--text-secondary);
        }
        .btn-reject:hover {
            background: var(--brb-red);
            color: white;
        }

        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-card);
            border: 1px solid var(--border);
            padding: 16px 24px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 1000;
            animation: slideDown 0.3s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }
        .notification.success { border-left: 4px solid var(--success); }
        .notification.warning { border-left: 4px solid var(--warning); }
        .notification.error { border-left: 4px solid var(--brb-red); }

        /* Audio Elements */
        .waiting-audio { display: none; }

        @media (max-width: 1024px) {
            .room-container { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .video-tile.local { width: 160px; height: 90px; bottom: 100px; right: 16px; }
        }
    </style>
</head>
<body>
    <div class="room-container">
        <div class="video-section">
            <div class="video-header">
                <div class="meeting-info">
                    <h1>{{ $meeting->title }}</h1>
                    <span>{{ $meeting->participants->count() }} участников</span>
                </div>
                <div class="timer">
                    <div class="timer-dot"></div>
                    <span class="timer-value" id="timer">00:00</span>
                </div>
            </div>

            <div class="video-grid" id="videoGrid">
                <div class="video-tile" id="remoteVideoTile">
                    <div class="video-placeholder" id="waitingPlaceholder">
                        <div class="avatar">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <p>Ожидание участников...</p>
                    </div>
                    <video id="remoteVideo" autoplay playsinline style="display: none;"></video>
                    <div class="video-label" id="remoteLabel" style="display: none;">
                        <span id="remoteName">Участник</span>
                        <i class="fa-solid fa-microphone-slash muted-icon" id="remoteMutedIcon" style="display: none;"></i>
                    </div>
                </div>
            </div>

            <div class="video-tile local" id="localVideoTile">
                <video id="localVideo" autoplay muted playsinline></video>
                <div class="video-label">
                    <span>Вы</span>
                    <i class="fa-solid fa-microphone-slash muted-icon" id="localMutedIcon" style="display: none;"></i>
                </div>
            </div>

            <div class="controls-bar">
                <button class="control-btn" id="toggleMute" title="Микрофон">
                    <i class="fa-solid fa-microphone" id="muteIcon"></i>
                </button>
                <button class="control-btn" id="toggleVideo" title="Камера">
                    <i class="fa-solid fa-video" id="videoIcon"></i>
                </button>
                <button class="control-btn" id="toggleScreen" title="Демонстрация экрана">
                    <i class="fa-solid fa-display"></i>
                </button>
                <button class="control-btn end-call" id="endCall" title="Завершить">
                    <i class="fa-solid fa-phone-slash"></i>
                </button>
            </div>
        </div>

        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-users"></i> Участники</h2>
            </div>
            <div class="participants-list" id="participantsList">
                <div id="waitingRequests"></div>
                <!-- Participants will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Notification Sound -->
    <audio id="notificationSound" src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU..." preload="auto"></audio>

    <script>
    const MEETING_ID = @json($meeting->id);
    const USER_ID = @json(auth()->id());
    const USER_NAME = @json(auth()->user()->name);
    const IS_HOST = @json($meeting->isHost(auth()->user()));

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    const ICE_SERVERS = @json($iceServers);
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    class VideoCall {
        constructor() {
            this.localStream = null;
            this.peerConnections = {};
            this.remoteStreams = {};
            this.isMuted = false;
            this.isVideoOff = false;
            this.startTime = Date.now();
            this.pollingInterval = null;
            this.waitingUsers = new Map();

            this.init();
        }

        async init() {
            try {
                await this.getLocalMedia();
                this.setupControls();
                this.startTimer();
                this.startPolling();
                this.showNotification('Вы присоединились к встрече', 'success');
            } catch (error) {
                console.error('Init error:', error);
                this.showNotification('Ошибка: ' + error.message, 'error');
            }
        }

        async getLocalMedia() {
            try {
                this.localStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: { echoCancellation: true, noiseSuppression: true }
                });
                document.getElementById('localVideo').srcObject = this.localStream;
            } catch (e) {
                // Fallback to audio only
                this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.isVideoOff = true;
                document.getElementById('videoIcon').className = 'fa-solid fa-video-slash';
                document.getElementById('toggleVideo').classList.add('active');
            }
        }

        createPeerConnection(userId, userName) {
            if (this.peerConnections[userId]) return this.peerConnections[userId];

            const pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });

            this.localStream.getTracks().forEach(track => {
                pc.addTrack(track, this.localStream);
            });

            pc.onicecandidate = (e) => {
                if (e.candidate) {
                    this.sendSignal('ice-candidate', { candidate: e.candidate.toJSON() }, userId);
                }
            };

            pc.ontrack = (e) => {
                // Remote track received
                if (!this.remoteStreams[userId]) {
                    this.remoteStreams[userId] = new MediaStream();
                }
                this.remoteStreams[userId].addTrack(e.track);
                this.showRemoteVideo(userId, userName);
            };

            pc.onconnectionstatechange = () => {
                // Connection state changed
                if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed') {
                    this.handleUserLeft(userId);
                }
            };

            this.peerConnections[userId] = pc;
            return pc;
        }

        async createOffer(userId, userName) {
            const pc = this.createPeerConnection(userId, userName);
            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            this.sendSignal('offer', { sdp: pc.localDescription.toJSON(), name: USER_NAME }, userId);
        }

        async handleOffer(userId, data) {
            const pc = this.createPeerConnection(userId, data.name || 'Участник');
            await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            this.sendSignal('answer', { sdp: pc.localDescription.toJSON(), name: USER_NAME }, userId);
        }

        async handleAnswer(userId, data) {
            const pc = this.peerConnections[userId];
            if (pc) {
                await pc.setRemoteDescription(new RTCSessionDescription(data.sdp));
            }
        }

        async handleIceCandidate(userId, data) {
            const pc = this.peerConnections[userId];
            if (pc && data.candidate) {
                await pc.addIceCandidate(new RTCIceCandidate(data.candidate));
            }
        }

        showRemoteVideo(userId, userName) {
            const placeholder = document.getElementById('waitingPlaceholder');
            const video = document.getElementById('remoteVideo');
            const label = document.getElementById('remoteLabel');
            const nameEl = document.getElementById('remoteName');

            placeholder.style.display = 'none';
            video.style.display = 'block';
            video.srcObject = this.remoteStreams[userId];
            label.style.display = 'flex';
            nameEl.textContent = userName || 'Участник';

            document.getElementById('videoGrid').classList.add('multiple');
        }

        handleUserLeft(userId) {
            if (this.peerConnections[userId]) {
                this.peerConnections[userId].close();
                delete this.peerConnections[userId];
            }
            delete this.remoteStreams[userId];

            document.getElementById('waitingPlaceholder').style.display = 'flex';
            document.getElementById('remoteVideo').style.display = 'none';
            document.getElementById('remoteLabel').style.display = 'none';
            document.getElementById('videoGrid').classList.remove('multiple');

            this.showNotification('Участник покинул встречу', 'warning');
        }

        // Show waiting request for host to accept
        showWaitingRequest(userId, userName) {
            if (this.waitingUsers.has(userId)) return;
            this.waitingUsers.set(userId, userName);

            const container = document.getElementById('waitingRequests');
            const card = document.createElement('div');
            card.className = 'waiting-card';
            card.id = 'waiting-' + userId;
            card.innerHTML = `
                <div class="waiting-card-header">
                    <div class="avatar">${escapeHtml(userName.charAt(0).toUpperCase())}</div>
                    <div class="info">
                        <h4>${escapeHtml(userName)}</h4>
                        <p>Хочет присоединиться</p>
                    </div>
                </div>
                <div class="waiting-card-actions">
                    <button class="btn-accept" data-user-id="${userId}" data-user-name="${escapeHtml(userName)}">
                        <i class="fa-solid fa-check me-1"></i> Принять
                    </button>
                    <button class="btn-reject" data-user-id="${userId}">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
            card.querySelector('.btn-accept').addEventListener('click', function() {
                videoCall.acceptUser(this.dataset.userId, this.dataset.userName);
            });
            card.querySelector('.btn-reject').addEventListener('click', function() {
                videoCall.rejectUser(this.dataset.userId);
            });
            container.appendChild(card);

            // Play notification sound
            this.playNotificationSound();
            this.showNotification(`${userName} хочет присоединиться`, 'warning');
        }

        async acceptUser(userId, userName) {
            // Remove waiting card
            const card = document.getElementById('waiting-' + userId);
            if (card) card.remove();
            this.waitingUsers.delete(userId);

            // Create offer to connect
            await this.createOffer(userId, userName);
            this.showNotification(`${userName} присоединился`, 'success');
        }

        rejectUser(userId) {
            const card = document.getElementById('waiting-' + userId);
            if (card) card.remove();
            this.waitingUsers.delete(userId);
            // Could send rejection signal here
        }

        async sendSignal(type, data, recipientId = null) {
            try {
                await fetch(`/admin/meetings/${MEETING_ID}/signal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify({ type, data, recipient_id: recipientId })
                });
            } catch (e) {
                console.error('Signal error:', e);
            }
        }

        async pollSignals() {
            try {
                const res = await fetch(`/admin/meetings/${MEETING_ID}/signals`);
                const { signals } = await res.json();

                for (const sig of signals) {
                    switch (sig.type) {
                        case 'join-request':
                            // Someone wants to join - show request if host
                            if (IS_HOST) {
                                this.showWaitingRequest(sig.sender_id, sig.data.name);
                            }
                            break;
                        case 'offer':
                            await this.handleOffer(sig.sender_id, sig.data);
                            break;
                        case 'answer':
                            await this.handleAnswer(sig.sender_id, sig.data);
                            break;
                        case 'ice-candidate':
                            await this.handleIceCandidate(sig.sender_id, sig.data);
                            break;
                    }
                }
            } catch (e) {
                console.error('Poll error:', e);
            }
        }

        async loadParticipants() {
            try {
                const res = await fetch(`/admin/meetings/${MEETING_ID}/participants`);
                const { participants } = await res.json();

                const container = document.getElementById('participantsList');
                const waitingContainer = document.getElementById('waitingRequests');

                // Keep waiting requests
                const html = participants.map(p => `
                    <div class="participant-card">
                        <div class="avatar">${escapeHtml(p.name.charAt(0).toUpperCase())}</div>
                        <div class="info">
                            <div class="name">${escapeHtml(p.name)}${p.id === USER_ID ? ' (Вы)' : ''}</div>
                            <div class="status ${p.status === 'joined' ? 'online' : ''}">${p.status === 'joined' ? 'В сети' : 'Приглашен'}</div>
                        </div>
                        <div class="icons">
                            <i class="fa-solid fa-microphone${p.is_muted ? '-slash off' : ''}"></i>
                            <i class="fa-solid fa-video${p.is_video_off ? '-slash off' : ''}"></i>
                        </div>
                    </div>
                `).join('');

                container.innerHTML = '';
                container.appendChild(waitingContainer);
                container.innerHTML += html;

            } catch (e) {
                console.error('Participants error:', e);
            }
        }

        startPolling() {
            // Send join request if not host
            if (!IS_HOST) {
                this.sendSignal('join-request', { name: USER_NAME });
            }

            this.pollingInterval = setInterval(() => {
                this.pollSignals();
                this.loadParticipants();
            }, 1000);

            this.loadParticipants();
        }

        startTimer() {
            setInterval(() => {
                const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
                const m = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const s = (elapsed % 60).toString().padStart(2, '0');
                document.getElementById('timer').textContent = `${m}:${s}`;
            }, 1000);
        }

        setupControls() {
            document.getElementById('toggleMute').addEventListener('click', () => {
                this.isMuted = !this.isMuted;
                this.localStream.getAudioTracks().forEach(t => t.enabled = !this.isMuted);

                const icon = document.getElementById('muteIcon');
                const btn = document.getElementById('toggleMute');
                const localIcon = document.getElementById('localMutedIcon');

                icon.className = this.isMuted ? 'fa-solid fa-microphone-slash' : 'fa-solid fa-microphone';
                btn.classList.toggle('active', this.isMuted);
                localIcon.style.display = this.isMuted ? 'inline' : 'none';

                this.notifyMediaChange('mute');
            });

            document.getElementById('toggleVideo').addEventListener('click', () => {
                this.isVideoOff = !this.isVideoOff;
                this.localStream.getVideoTracks().forEach(t => t.enabled = !this.isVideoOff);

                const icon = document.getElementById('videoIcon');
                const btn = document.getElementById('toggleVideo');

                icon.className = this.isVideoOff ? 'fa-solid fa-video-slash' : 'fa-solid fa-video';
                btn.classList.toggle('active', this.isVideoOff);

                this.notifyMediaChange('video');
            });

            document.getElementById('toggleScreen').addEventListener('click', async () => {
                try {
                    const screen = await navigator.mediaDevices.getDisplayMedia({ video: true });
                    const videoTrack = screen.getVideoTracks()[0];

                    Object.values(this.peerConnections).forEach(pc => {
                        const sender = pc.getSenders().find(s => s.track?.kind === 'video');
                        if (sender) sender.replaceTrack(videoTrack);
                    });

                    document.getElementById('localVideo').srcObject = screen;
                    document.getElementById('toggleScreen').classList.add('active');

                    videoTrack.onended = () => {
                        const originalTrack = this.localStream.getVideoTracks()[0];
                        Object.values(this.peerConnections).forEach(pc => {
                            const sender = pc.getSenders().find(s => s.track?.kind === 'video');
                            if (sender && originalTrack) sender.replaceTrack(originalTrack);
                        });
                        document.getElementById('localVideo').srcObject = this.localStream;
                        document.getElementById('toggleScreen').classList.remove('active');
                    };
                } catch (e) {
                    console.error('Screen share error:', e);
                }
            });

            document.getElementById('endCall').addEventListener('click', () => {
                if (confirm('Покинуть встречу?')) this.endCall();
            });

            window.addEventListener('beforeunload', () => this.endCall(false));
        }

        async notifyMediaChange(type) {
            try {
                await fetch(`/admin/meetings/${MEETING_ID}/toggle-media`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({ type })
                });
            } catch (e) {}
        }

        async endCall(redirect = true) {
            clearInterval(this.pollingInterval);
            this.localStream?.getTracks().forEach(t => t.stop());
            Object.values(this.peerConnections).forEach(pc => pc.close());

            try {
                await fetch(`/admin/meetings/${MEETING_ID}/leave`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
            } catch (e) {}

            if (redirect) window.location.href = '/admin/meetings';
        }

        showNotification(message, type = 'success') {
            const existing = document.querySelector('.notification');
            if (existing) existing.remove();

            const el = document.createElement('div');
            el.className = 'notification ' + type;
            el.innerHTML = `<i class="fa-solid fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-circle' : 'times-circle'}"></i><span>${escapeHtml(message)}</span>`;
            document.body.appendChild(el);

            setTimeout(() => el.remove(), 4000);
        }

        playNotificationSound() {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleXNxqeLyxINTKUSV2fPhopJyYpXa8fK9i2AyPZDV9O2ymXRlmNz07bySVCc8ktj16rmZc2OX2fPtuJJYMkCT1/TqupxzY5bb9Oy5kFQpO5DW9O26mnJildn07LmSVyw/kdb07bqbcmKU2PTsuJFUKTqQ1vTtuptyYpXZ9Oy5klcsP5HV9O26m3JilNj07LiRVCk6kNb07bqbcmKV2fTsuZJXLD+R1fTtuptx');
            audio.volume = 0.5;
            audio.play().catch(() => {});
        }
    }

    const videoCall = new VideoCall();
    </script>
</body>
</html>
