<button type="button" class="btn btn-b btn-sm" data-toggle="collapse" data-target="#changePassword">
    Change password
</button>
<div id="changePassword" class="collapse">
    <form method="POST" autocomplete="off" class="mt-5" action="{{$change_password_route}}">
        @csrf
        <div class="form-group">
            <label class="control-label">Enter your current password</label>
            <input type="password" name="password" class="form-control"  autocomplete="off" data-lpignore="true" required>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">New password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control"
                        data-parsley-minlength="8"
                        data-parsley-number="1"
                        required
                        autocomplete="off"
                    >
                    <small>Min 8 characters</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Reenter new password</label>
                    <input type="password" name="new_password_conf" class="form-control" 
                        data-parsley-equalto="#new_password"
                        required
                        autocomplete="off"
                    >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="submit" name="store" class="btn btn-primary float-right">Save password</button>
            </div>
        </div>
    </form>
</div>