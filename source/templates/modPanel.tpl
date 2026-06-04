{template:modNav}
<p class="centerItem">{$TOTAL_PASTES} paste(s) total.</p>
{if:HAS_PASTES}
<form method="post" action="?route=moderateRoute">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="csrf_token" value="{$CSRF_TOKEN}">
    <div class="modDeleteBar">
        <button type="button" id="selectAllBtn">Select all</button>
        <input type="submit" value="Delete selected" onclick="return confirm('Delete selected pastes?')">
    </div>
    <table class="postlists modTable">
        <thead>
            <tr>
                <th class="nowrap"></th>
                <th class="nowrap">ID</th>
                <th>Title</th>
                <th class="nowrap">IP</th>
                <th class="nowrap">Created</th>
                <th class="nowrap">Expires</th>
                <th>Preview</th>
            </tr>
        </thead>
        <tbody>
            {foreach:PASTES as paste}
            <tr>
                <td class="nowrap"><input type="checkbox" class="rowCheck" name="ids[]" value="{$paste.id}"></td>
                <td class="nowrap"><a href="?route=viewPasta&amp;id={$paste.uuid}">{$paste.id}</a></td>
                <td>{$paste.title}</td>
                <td class="nowrap">{$paste.ip_address}</td>
                <td class="nowrap">{$paste.created_at}</td>
                <td class="nowrap">{$paste.ttl_label}</td>
                <td>{$paste.content_preview}</td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="modDeleteBar">
        <button type="button" id="selectAllBtn2">Select all</button>
        <input type="submit" value="Delete selected" onclick="return confirm('Delete selected pastes?')">
    </div>
</form>
<script>
var allSelected = false;
function toggleSelectAll(btn1, btn2, cls) {
    allSelected = !allSelected;
    document.querySelectorAll(cls).forEach(function (cb) { cb.checked = allSelected; });
    var label = allSelected ? 'Deselect all' : 'Select all';
    btn1.textContent = label;
    btn2.textContent = label;
}
document.getElementById('selectAllBtn').addEventListener('click', function () {
    toggleSelectAll(this, document.getElementById('selectAllBtn2'), '.rowCheck');
});
document.getElementById('selectAllBtn2').addEventListener('click', function () {
    toggleSelectAll(document.getElementById('selectAllBtn'), this, '.rowCheck');
});
</script>
{else}
<p class="centerItem">No pastes found.</p>
{/if}
{$PAGINATION}
