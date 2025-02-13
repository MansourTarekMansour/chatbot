<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Assistant</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>
<body>
    <div class="chat-container">
        <!-- Left Sidebar -->
        <div class="sidebar">
            <button class="new-chat-btn" id="newChatBtn">
                <i class="fas fa-plus"></i> New Chat
            </button>
            <div class="chat-list" id="chatList">
                @foreach ($chats as $chat)
                    <div class="chat-item" data-chat-id="{{ $chat->id }}">
                        <span>{{ $chat->title }}</span>
                        <button class="delete-btn" data-chat-id="{{ $chat->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Chat Window -->
        <div class="chat-window">
            <div class="messages-container" id="messagesContainer">
                @if($activeChat)
                    @foreach($activeChat->messages as $message)
                        <div class="message {{ $message->role === 'user' ? 'user-message' : 'assistant-message' }}">
                            {{ $message->content }}
                            <div class="timestamp">
                                {{ $message->created_at->format('h:i A') }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <div class="input-container">
                <div class="input-group">
                    <textarea 
                        id="messageInput"
                        class="form-control"
                        placeholder="Type your message..."
                        rows="1"
                        style="resize: none; border-radius: 0.375rem;"
                    ></textarea>
                    <div class="input-group-append">
                        <button id="sendBtn" class="btn" style="background: var(--primary-color); color: white;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let activeChatId = {{ $activeChat->id ?? 'null' }};

            // Load chats initially
            loadChats();

            // New Chat Button
            $('#newChatBtn').click(function() {
                $.post('/chats', function(response) {
                    loadChats();
                    loadChat(response.id);
                });
            });

            // Delete Chat
            $(document).on('click', '.delete-btn', function(e) {
                e.stopPropagation();
                const chatId = $(this).data('chat-id');
                
                $.ajax({
                    url: `/chats/${chatId}`,
                    type: 'DELETE',
                    success: function() {
                        loadChats();
                        if (activeChatId === chatId) {
                            $('#messagesContainer').empty();
                            activeChatId = null;
                        }
                    }
                });
            });

            // Load specific chat
            function loadChat(chatId) {
                activeChatId = chatId;
                $.get(`/chats/${chatId}`, function(chat) {
                    $('#messagesContainer').empty();
                    chat.messages.forEach(message => {
                        appendMessage(message);
                    });
                    scrollToBottom();
                });
            }

            // Send Message
            $('#sendBtn').click(sendMessage);
            $('#messageInput').keypress(function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            function sendMessage() {
                const content = $('#messageInput').val().trim();
                if (!content || !activeChatId) return;

                // Add user message immediately
                appendMessage({
                    content: content,
                    role: 'user',
                    created_at: new Date().toISOString()
                });

                $.post(`/chats/${activeChatId}/messages`, { content }, function(response) {
                    appendMessage(response.assistant_response);
                    scrollToBottom();
                });

                $('#messageInput').val('');
            }

            function appendMessage(message) {
                const isUser = message.role === 'user';
                const time = new Date(message.created_at).toLocaleTimeString([], { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });

                $('#messagesContainer').append(`
                    <div class="message ${isUser ? 'user-message' : 'assistant-message'}">
                        ${message.content}
                        <div class="timestamp">${time}</div>
                    </div>
                `);
            }

            function scrollToBottom() {
                const container = $('#messagesContainer')[0];
                container.scrollTop = container.scrollHeight;
            }

            function loadChats() {
                $.get('/chats', function(chats) {
                    $('#chatList').empty();
                    chats.forEach(chat => {
                        $('#chatList').append(`
                            <div class="chat-item" data-chat-id="${chat.id}">
                                <span>${chat.title}</span>
                                <button class="delete-btn" data-chat-id="${chat.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `);
                    });
                });
            }

            // Handle chat item clicks
            $(document).on('click', '.chat-item', function() {
                const chatId = $(this).data('chat-id');
                loadChat(chatId);
            });
        });
    </script>
</body>
</html>