<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <title>Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="container-fluid h-100">
        <div class="row justify-content-center h-100">
            <x-chat-list />
            <x-chat />
        </div>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            loadChats();

            // Add a new chat
            $(document).on('click', '#add_new_chat', function() {
                $.post("{{ route('chats.store') }}", function(chat) {
                    $('.contacts').append(`
                        <li data-chat-id="${chat.id}">
                            <div class="d-flex bd-highlight">
                                <div class="user_info"><span>${chat.title || "Chat " + chat.id}</span></div>
                                <div class="delete_chat" style="margin-left: auto; cursor: pointer;">×</div>
                            </div>
                        </li>`);
                    navigateToChat(chat.id);
                }).fail(function(error) {
                    console.error("Error creating chat:", error);
                });
            });

            // Submit message via button click or enter key
            $(document).on('click', '#button-submit', function(event) {
                event.preventDefault();
                sendMessage();
            });

            $(document).on('keypress', '#input', function(event) {
                if (event.which == 13) {
                    event.preventDefault();
                    sendMessage();
                }
            });

            // Navigate to selected chat and load messages
            $(document).on('click', '.contacts li', function() {
                $('.contacts li').removeClass('active');
                $(this).addClass('active');
                let chatId = $(this).data('chat-id');
                loadMessages(chatId);
            });

            // Delete a chat
            $(document).on('click', '.delete_chat', function() {
                let chatId = $(this).closest('li').data('chat-id');
                $.ajax({
                    type: "DELETE",
                    url: `/chats/${chatId}`,
                    success: function() {
                        $(`.contacts li[data-chat-id="${chatId}"]`).remove();
                        if ($('.contacts li').length > 0) {
                            navigateToChat($('.contacts li').first().data('chat-id'));
                        } else {
                            $('#content-box').empty();
                        }
                    },
                    error: function(error) {
                        console.error("Error deleting chat:", error);
                    }
                });
            });
        });

        // Load chats
        function loadChats() {
            $.get("{{ route('chats.index') }}", function(chats) {
                $('.contacts').empty();
                chats.forEach(chat => {
                    $('.contacts').append(`
                        <li data-chat-id="${chat.id}">
                            <div class="d-flex bd-highlight">
                                <div class="user_info"><span>${chat.title || "Chat " + chat.id}</span></div>
                                <div class="delete_chat" style="margin-left: auto; cursor: pointer;">×</div>
                            </div>
                        </li>`);
                });
                if (chats.length > 0) {
                    navigateToChat(chats[0].id);
                }
            }).fail(function(error) {
                console.error("Error loading chats:", error);
            });
        }

        // Load messages for a specific chat
        function loadMessages(chatId) {
            $.get(`/chats/${chatId}`, function(messages) {
                $('#content-box').empty();
                messages.forEach(msg => {
                    const isUser = msg.role === 'user';
                    $('#content-box').append(`
                        <div class="d-flex justify-content-${isUser ? "end" : "start"} mb-4">
                            <div class="msg_cotainer${isUser ? "_send" : ""}">
                                ${msg.content}
                                <span class="msg_time">${new Date(msg.created_at).toLocaleTimeString()}</span>
                            </div>
                        </div>`);
                });
                scrollToBottom();
            }).fail(function(error) {
                console.error("Error loading messages:", error);
            });
        }

        // Send a message
        function sendMessage() {
            let messageContent = $('#input').val().trim();
            let chatId = $('.contacts li.active').data('chat-id');
            if (!chatId || !messageContent) return;

            $('#content-box').append(`
                <div class="d-flex justify-content-end mb-4">
                    <div class="msg_cotainer_send">${messageContent}
                        <span class="msg_time_send">${new Date().toLocaleTimeString()}</span>
                    </div>
                </div>`);
            $('#input').val('');
            scrollToBottom();

            // Send message to the backend
            $.post(`/chats/${chatId}/messages`, {
                content: messageContent,
                role: 'user' // Add the 'role' field here
            }).done(function(response) {
                if (response.message) {
                    $('#content-box').append(`
                        <div class="d-flex justify-content-start mb-4">
                            <div class="msg_cotainer">${response.message.content || response.message}</div>
                            <span class="msg_time">${new Date().toLocaleTimeString()}</span>
                        </div>`);
                    // Append assistant response if available
                    if (response.assistant_response && response.assistant_response.content) {
                        $('#content-box').append(`
                            <div class="d-flex justify-content-start mb-4">
                                <div class="msg_cotainer">${response.assistant_response.content}</div>
                                <span class="msg_time">${new Date().toLocaleTimeString()}</span>
                            </div>`);
                    }
                    scrollToBottom();
                }
            }).fail(function(error) {
                console.error("Error sending message:", error);
            });
        }

        // Navigate to a specific chat
        function navigateToChat(chatId) {
            $('.contacts li').removeClass('active');
            $(`.contacts li[data-chat-id="${chatId}"]`).addClass('active');
            loadMessages(chatId);
        }

        // Scroll to the bottom of the chat container
        function scrollToBottom() {
            let chatBox = $('#content-box');
            chatBox.scrollTop(chatBox.prop("scrollHeight"));
        }
    </script>
</body>

</html>
