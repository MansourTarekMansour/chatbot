<div class="col-md-8 col-xl-9 chat">
    <div class="card">
        <div class="card-header msg_head">
            <div class="d-flex bd-highlight">
                <div class="img_cont">
                    <img src="https://static.turbosquid.com/Preview/001292/481/WV/_D.jpg" class="rounded-circle user_img">
                    <span class="online_icon"></span>
                </div>
                <div class="user_info">
                    <span>OpenAI Chatbot</span>
                </div>
            </div>
            <span id="action_menu_btn"><i class="fas fa-ellipsis-v"></i></span>
            <div class="action_menu">
                <ul>
                    <li id="add_new_chat"><i class="fas fa-user-circle"></i> Add new chat</li>
                    <li id="delete_chat"><i class="fas fa-users"></i> Delete chat</li>
                </ul>
            </div>
        </div>
        <div id="content-box" class="card-body msg_card_body">
            <!-- Chat messages will be appended here -->
        </div>
        <div class="card-footer">
            <div class="input-group">
                <div class="input-group-append">
                    <span class="input-group-text attach_btn"><i class="fas fa-paperclip"></i></span>
                </div>
                <textarea id="input" name="input" class="form-control type_msg" placeholder="Type your message..."></textarea>
                <div id="button-submit" class="input-group-append">
                    <span class="input-group-text send_btn"><i class="fas fa-location-arrow"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>