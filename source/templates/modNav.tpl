<h2 class="centerItem">Moderation Panel</h2>
<div class="modPanelHeader centerItem">
    Logged in as <b>{$MOD_USER}</b> &mdash;
    <form method="post" action="?route=moderateRoute" class="inlineForm">
        <input type="hidden" name="action" value="logout">
        <input type="submit" value="Log out">
    </form>
</div>
<nav class="modNav centerItem">
    {if:NAV_PASTES}<b>Pastes</b>{else}<a href="?route=moderateRoute">Pastes</a>{/if} | {if:NAV_ACCOUNT}<b>Account</b>{else}<a href="?route=moderateRoute&amp;subpage=account">Account</a>{/if}{if:SHOW_ACCOUNTS_LINK} | <a href="?route=moderateRoute&amp;subpage=accounts">Accounts</a>{/if}{if:SHOW_ACCOUNTS_BOLD} | <b>Accounts</b>{/if}
</nav>
<hr>