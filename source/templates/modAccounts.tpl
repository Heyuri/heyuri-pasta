{template:modNav}
<h3 class="centerItem">Create Account</h3>
{if:CREATE_SUCCESS}
<p class="centerItem success">{$CREATE_SUCCESS}</p>
{/if}
{if:CREATE_ERROR}
<p class="centerItem error">{$CREATE_ERROR}</p>
{/if}
<div class="loginFormContainer">
    <form method="post" action="?route=moderateRoute&amp;subpage=accounts">
        <input type="hidden" name="action" value="createMod">
        <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}">
        <table class="loginTable">
            <tr>
                <td class="postblock">Username</td>
                <td><input type="text" name="new_username" maxlength="50"></td>
            </tr>
            <tr>
                <td class="postblock">Role</td>
                <td>
                    <select name="new_role">
                        <option value="mod">mod</option>
                        <option value="admin">admin</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="postblock">Password</td>
                <td><input type="password" name="new_password"></td>
            </tr>
            <tr>
                <td class="postblock">Confirm password</td>
                <td><input type="password" name="new_confirm"></td>
            </tr>
        </table>
        <div class="centerItem"><input type="submit" value="Create account"></div>
    </form>
</div>
<h3 class="centerItem">Accounts</h3>
{if:DELETE_SUCCESS}
<p class="centerItem success">{$DELETE_SUCCESS}</p>
{/if}
{if:DELETE_ERROR}
<p class="centerItem error">{$DELETE_ERROR}</p>
{/if}
{if:HAS_ACCOUNTS}
<form method="post" action="?route=moderateRoute&amp;subpage=accounts">
    <input type="hidden" name="action" value="deleteAccounts">
    <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}">
    <div class="modDeleteBar">
        <button type="button" id="selectAllAccBtn">Select all</button>
        <input type="submit" value="Delete selected" onclick="return confirm('Delete selected accounts?')">
    </div>
    <table class="postlists modTable">
        <thead>
            <tr>
                <th class="nowrap"></th>
                <th class="nowrap">ID</th>
                <th>Username</th>
                <th class="nowrap">Role</th>
                <th class="nowrap">Created</th>
            </tr>
        </thead>
        <tbody>
            {foreach:ACCOUNTS as account}
            <tr>
                <td class="nowrap"><input type="checkbox" class="accountCheck" name="ids[]" value="{$account.id}"></td>
                <td class="nowrap">{$account.id}</td>
                <td>{$account.username}</td>
                <td class="nowrap">{$account.role}</td>
                <td class="nowrap">{$account.created_at}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="modDeleteBar">
        <button type="button" id="selectAllAccBtn2">Select all</button>
        <input type="submit" value="Delete selected" onclick="return confirm('Delete selected accounts?')">
    </div>
</form>
<script>
var allAccSelected = false;
function toggleSelectAllAcc(btn1, btn2) {
    allAccSelected = !allAccSelected;
    document.querySelectorAll('.accountCheck').forEach(function (cb) { cb.checked = allAccSelected; });
    var label = allAccSelected ? 'Deselect all' : 'Select all';
    btn1.textContent = label;
    btn2.textContent = label;
}
document.getElementById('selectAllAccBtn').addEventListener('click', function () {
    toggleSelectAllAcc(this, document.getElementById('selectAllAccBtn2'));
});
document.getElementById('selectAllAccBtn2').addEventListener('click', function () {
    toggleSelectAllAcc(document.getElementById('selectAllAccBtn'), this);
});
</script>
{else}
<p class="centerItem">No accounts found.</p>
{/if}
<h3 class="centerItem">Set Password</h3>
{if:SETPW_SUCCESS}
<p class="centerItem success">{$SETPW_SUCCESS}</p>
{/if}
{if:SETPW_ERROR}
<p class="centerItem error">{$SETPW_ERROR}</p>
{/if}
<div class="loginFormContainer">
    <form method="post" action="?route=moderateRoute&amp;subpage=accounts">
        <input type="hidden" name="action" value="adminSetPassword">
        <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}">
        <table class="loginTable">
            <tr>
                <td class="postblock">Account</td>
                <td>
                    <select name="target_username">
                        {foreach:ACCOUNTS as account}
                        <option value="{$account.username}">{$account.username} ({$account.role})</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="postblock">New password</td>
                <td><input type="password" name="set_password"></td>
            </tr>
            <tr>
                <td class="postblock">Confirm password</td>
                <td><input type="password" name="set_confirm"></td>
            </tr>
        </table>
        <div class="centerItem"><input type="submit" value="Set password"></div>
    </form>
</div>
