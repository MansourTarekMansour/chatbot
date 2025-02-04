<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .chat-container { height: 100vh; }
        .contacts-card { height: calc(100% - 60px); }
        .msg-card-body { overflow-y: auto; height: calc(100% - 120px); }
        .msg_container, .msg_container_send { max-width: 80%; margin: 5px; padding: 10px; border-radius: 15px; }
        .msg_container { background: #f8f9fa; }
        .msg_container_send { background: #007bff; color: white; }
        .msg_time { font-size: 0.75rem; display: block; margin-top: 5px; }
    </style>
</head>
<body>
@php
    $chats = $chats ?? [];
    $activeChat = $activeChat ?? null;
@endphp
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Chat List -->
            <div class="col-md-4 col-xl-3 p-0">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Chats</h5>
                    </div>
                    <div class="card-body contacts-card p-0">
                        <ul class="list-unstyled contacts mb-0" id="chat-list">
                            @foreach($chats as $chat)
                                <li class="p-3 border-bottom chat-item" data-chat-id="{{ $chat->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ $chat->title ?: 'Chat #'.$chat->id }}</span>
                                        <button class="btn btn-sm btn-danger delete-chat">×</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary btn-block" id="new-chat">New Chat</button>
                    </div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="col-md-8 col-xl-9 p-0">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <h5 class="mb-0">AI Chat Assistant</h5>
                    </div>
                    <div class="card-body msg-card-body" id="chat-window">
                        @if(isset($activeChat))
                            @foreach($activeChat->messages as $message)
                                <div class="d-flex justify-content-{{ $message->role === 'user' ? 'end' : 'start' }} mb-3">
                                    <div class="msg_container{{ $message->role === 'user' ? '_send' : '' }}">
                                        {{ $message->content }}
                                        <span class="msg_time">{{ $message->created_at->format('H:i') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="input-group">
                            <textarea id="message-input" class="form-control" placeholder="Type your message..." rows="1"></textarea>
                            <div class="input-group-append">
                                <button id="send-btn" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            let activeChatId = {{ $activeChat->id ?? 'null' }};

            // New Chat
            $('#new-chat').click(function() {
                $.post('/chats', function(response) {
                    const chat = response;
                    $('#chat-list').append(`
                        <li class="p-3 border-bottom chat-item" data-chat-id="${chat.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Chat #${chat.id}</span>
                                <button class="btn btn-sm btn-danger delete-chat">×</button>
                            </div>
                        </li>
                    `);
                    loadChat(chat.id);
                });
            });

            // Load Chat
            function loadChat(chatId) {
                activeChatId = chatId;
                $.get(`/chats/${chatId}`, function(chat) {
                    $('#chat-window').empty();
                    chat.messages.forEach(msg => {
                        appendMessage(msg);
                    });
                    scrollToBottom();
                });
            }

            // Send Message
            $('#send-btn').click(sendMessage);
            $('#message-input').keypress(function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            function sendMessage() {
                const content = $('#message-input').val().trim();
                if (!content || !activeChatId) return;

                $.post(`/chats/${activeChatId}/messages`, { content }, function(response) {
                    $('#message-input').val('');
                    appendMessage(response.user_message);
                    appendMessage({
                        content: response.assistant_response,
                        role: 'assistant',
                        created_at: new Date().toISOString()
                    });
                    scrollToBottom();
                });
            }

            function appendMessage(msg) {
                const isUser = msg.role === 'user';
                $('#chat-window').append(`
                    <div class="d-flex justify-content-${isUser ? 'end' : 'start'} mb-3">
                        <div class="msg_container${isUser ? '_send' : ''}">
                            ${msg.content}
                            <span class="msg_time">${new Date(msg.created_at).toLocaleTimeString()}</span>
                        </div>
                    </div>
                `);
            }

            // Delete Chat
            $(document).on('click', '.delete-chat', function() {
                const chatId = $(this).closest('.chat-item').data('chat-id');
                $.ajax({
                    url: `/chats/${chatId}`,
                    type: 'DELETE',
                    success: function() {
                        $(`.chat-item[data-chat-id="${chatId}"]`).remove();
                        if (activeChatId === chatId) {
                            activeChatId = null;
                            $('#chat-window').empty();
                        }
                    }
                });
            });

            // Select Chat
            $(document).on('click', '.chat-item', function() {
                $('.chat-item').removeClass('active');
                $(this).addClass('active');
                loadChat($(this).data('chat-id'));
            });

            function scrollToBottom() {
                $('#chat-window').scrollTop($('#chat-window')[0].scrollHeight);
            }
        });
    </script>
</body>
</html>