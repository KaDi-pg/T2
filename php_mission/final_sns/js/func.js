function pushNewpostButton(valid)
{
    if (valid) {
        //投稿フォームを表示
        var formID = document.getElementById("post_form");
        formID.style.display = 'block';

        var openButton = document.getElementById("newpost_open_button");
        openButton.style.display = 'none';
    } else {
        //投稿できない旨を伝える
        alert("投稿には、ログインが必要です。");

    }
}

function closeNewpostArea()
{
    var formID = document.getElementById("post_form");
    formID.style.display = 'none';

    var openButton = document.getElementById("newpost_open_button");
    openButton.style.display = 'block';
}