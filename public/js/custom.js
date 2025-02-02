// $(document).ready(function () {
//     // Set up AJAX headers for CSRF protection
//     $.ajaxSetup({
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         }
//     });

//     // Load all chats on page load
//     loadChats();

//     // Toggle action menu
//     $("#action_menu_btn").click(function () {
//         $(".action_menu").toggle();
//     });

//     // Add new chat
//     $("#add_new_chat").on("click", function () {
//         $.ajax({
//             type: "POST",
//             url: "{{ route('chats.store') }}",
//             success: function (chat) {
//                 $(".contacts").append(`
//                     <li data-chat-id="${chat.id}">
//                         <div class="d-flex bd-highlight">
//                             <div class="user_info">
//                                 <span>${chat.title || "Chat " + chat.id}</span>
//                             </div>
//                             <div class="delete_chat" style="margin-left: auto; cursor: pointer;">×</div>
//                         </div>
//                     </li>
//                 `);
//                 navigateToChat(chat.id);
//             },
//             error: function (error) {
//                 console.error("Error creating chat:", error);
//             }
//         });
//     });

//     // Send message
//     $("#button-submit").on("click", function () {
//         const messageContent = $("#input").val().trim();
//         if (!messageContent) return;

//         const activeChat = $(".contacts li.active");
//         if (!activeChat.length) return;

//         const chatId = activeChat.data("chat-id");

//         $("#content-box").append(`
//             <div class="d-flex justify-content-end mb-4">
//                 <div class="msg_cotainer_send">
//                     ${messageContent}
//                     <span class="msg_time_send">${new Date().toLocaleTimeString()}</span>
//                 </div>
//             </div>
//         `);
//         $("#input").val("");

//         $.ajax({
//             type: "POST",
//             url: `/chats/${chatId}/messages`,
//             data: { message: messageContent },
//             success: function (response) {
//                 if (response.content) {
//                     $("#content-box").append(`
//                         <div class="d-flex justify-content-start mb-4">
//                             <div class="msg_cotainer">
//                                 ${response.content}
//                                 <span class="msg_time">${new Date().toLocaleTimeString()}</span>
//                             </div>
//                         </div>
//                     `);
//                 }
//             },
//             error: function (error) {
//                 console.error("Error sending message:", error);
//             }
//         });
//     });

//     // Delete a chat
//     $(document).on("click", ".delete_chat", function () {
//         const chatId = $(this).closest("li").data("chat-id");

//         $.ajax({
//             type: "DELETE",
//             url: `/chats/${chatId}`,
//             success: function () {
//                 $(`.contacts li[data-chat-id="${chatId}"]`).remove();
//                 const remainingChats = $(".contacts li");
//                 if (remainingChats.length > 0) {
//                     navigateToChat(remainingChats.first().data("chat-id"));
//                 } else {
//                     $("#content-box").empty();
//                 }
//             },
//             error: function (error) {
//                 console.error("Error deleting chat:", error);
//             }
//         });
//     });
// });

// // Load chats from database
// function loadChats() {
//     $.ajax({
//         type: "GET",
//         url: "{{ route('chats.index') }}",
//         success: function (chats) {
//             $(".contacts").empty();
//             chats.forEach(chat => {
//                 $(".contacts").append(`
//                     <li data-chat-id="${chat.id}">
//                         <div class="d-flex bd-highlight">
//                             <div class="user_info">
//                                 <span>${chat.title || "Chat " + chat.id}</span>
//                             </div>
//                             <div class="delete_chat" style="margin-left: auto; cursor: pointer;">×</div>
//                         </div>
//                     </li>
//                 `);
//             });
//             if (chats.length > 0) {
//                 navigateToChat(chats[0].id);
//             }
//         },
//         error: function (error) {
//             console.error("Error loading chats:", error);
//         }
//     });
// }

// // Load messages for a chat
// function navigateToChat(chatId) {
//     $(".contacts li").removeClass("active");
//     $(`.contacts li[data-chat-id="${chatId}"]`).addClass("active");

//     $.ajax({
//         type: "GET",
//         url: `/chats/${chatId}`,
//         success: function (messages) {
//             $("#content-box").empty();
//             messages.forEach(msg => {
//                 const isUser = msg.role === 'user';
//                 $("#content-box").append(`
//                     <div class="d-flex justify-content-${isUser ? "end" : "start"} mb-4">
//                         <div class="msg_cotainer${isUser ? "_send" : ""}">
//                             ${msg.content}
//                             <span class="msg_time">${new Date(msg.created_at).toLocaleTimeString()}</span>
//                         </div>
//                     </div>
//                 `);
//             });
//         },
//         error: function (error) {
//             console.error("Error loading messages:", error);
//         }
//     });
// }
