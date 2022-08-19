
<div class="card chat-app">

    <div class="chat">

        <div class="chat-header clearfix">
            <div class="row">
                <div class="col-lg-6">
                    <div class="chat-about">
                        <h6 class="m-b-0">Chat</h6>
                        <small>2 new messages</small>
                    </div>
                </div>
                <div class="col-lg-6 hidden-sm text-right">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search...">
                        <span class="input-group-addon">
                            <i class="zmdi zmdi-search"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="chat-history">
            <ul class="m-b-0">
                <li class="clearfix">
                    <div class="message-data text-right">
                        <span class="message-data-time" >10:10 AM, Today</span>
                        <img src="../assets/images/xs/avatar7.jpg" alt="avatar">
                    </div>
                    <div class="message other-message float-right"> Hi Aiden, how are you? How is the project coming along? </div>
                </li>
                <li class="clearfix">
                    <div class="message-data">
                        <span class="message-data-time">10:12 AM, Today</span>
                    </div>
                    <div class="message my-message">Are we meeting today?</div>                                    
                </li>                               
                <li class="clearfix">
                    <div class="message-data">
                        <span class="message-data-time">10:15 AM, Today</span>
                    </div>
                    <div class="message my-message">Project has been already finished and I have results to show you.</div>
                </li>
                <li class="clearfix">
                    <div class="message-data">
                        <span class="message-data-time">10:15 AM, Today</span>
                    </div>
                    <div class="message my-message">Project has been already finished and I have results to show you.</div>
                </li>
            </ul>
        </div>
        <div class="chat-message clearfix">
            <div class="input-group m-b-0">
                <input type="text" class="form-control" placeholder="Enter text here...">
                <span class="input-group-addon"><i class="zmdi zmdi-mail-send"></i></span>
            </div>
        </div>
    </div>

</div>


@section('page-styles')
<link rel="stylesheet" href="{{ asset('assets/css/chatapp.css') }}">
@stop