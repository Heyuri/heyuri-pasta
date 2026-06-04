<h2 class="centerItem">Login</h2>
<div class="loginFormContainer">
    {if:LOGIN_ERROR}
    <p class="error">{$LOGIN_ERROR}</p>
    {/if}
    <form method="post" action="?route=moderateRoute">
        <input type="hidden" name="action" value="login">
        <table class="loginTable">
            <tr>
                <td class="postblock">Username</td>
                <td><input type="text" name="username"></td>
            </tr>
            <tr>
                <td class="postblock">Password</td>
                <td><input type="password" name="password"</td>
            </tr>
        </table>
        <div class="centerItem"><input type="submit" value="Log in"></div>
    </form>
</div>
