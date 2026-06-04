<h2 class="centerItem">{$CREATE_PASTE_HEADER}</h2>
    <div class="createPasteFormContainer">
        <form class="createPasteForm" id="pastaForm" method="post" action="?route=createPasta">
            <table class="createPasteTable">
                <tr>
                    <td class="postblock">
                        Title
                    </td>
                    <td>
                        <input type="text" name="title" placeholder="Enter a title...">
                    </td>
                </tr>
                <tr>
                    <td class="postblock">
                        Text
                    </td>
                    <td>
                        <textarea class="pasteContentInput" name="content" placeholder="Enter your pasta content here."></textarea>
                    </td>
                </tr>
                <tr>
                    <td class="postblock">
                        Expire in
                    </td>
                    <td>
                        {$EXPIRE_TIMES}
                    </td>
                </tr>
            </table>
            <div class="centerItem"><input type="submit" value="New pasta"></div>
        </form>
    </div>