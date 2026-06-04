{template:modNav}
<h3 class="centerItem">Account</h3>
<div class="centerItem">
    <table class="loginTable">
        <tr>
            <td class="postblock">Username</td>
            <td>{$MOD_USER}</td>
        </tr>
        <tr>
            <td class="postblock">Role</td>
            <td>{$MOD_ROLE}</td>
        </tr>
        <tr>
            <td class="postblock">Member since</td>
            <td>{$MOD_CREATED}</td>
        </tr>
    </table>
</div>
<h3 class="centerItem">Change Password</h3>
{if:PASSWORD_SUCCESS}
<p class="centerItem success">{$PASSWORD_SUCCESS}</p>
{/if}
{if:PASSWORD_ERROR}
<p class="centerItem error">{$PASSWORD_ERROR}</p>
{/if}
<div class="loginFormContainer">
    <form method="post" action="?route=moderateRoute&amp;subpage=account">
        <input type="hidden" name="action" value="changePassword">
        <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}">
        <table class="loginTable">
            <tr>
                <td class="postblock">Current password</td>
                <td><input type="password" name="current_password"></td>
            </tr>
            <tr>
                <td class="postblock">New password</td>
                <td><input type="password" name="new_password"></td>
            </tr>
            <tr>
                <td class="postblock">Confirm new password</td>
                <td><input type="password" name="confirm_password"></td>
            </tr>
        </table>
        <div class="centerItem"><input type="submit" value="Change password"></div>
    </form>
</div>
