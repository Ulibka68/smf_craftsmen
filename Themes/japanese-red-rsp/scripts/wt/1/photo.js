
// let photobtn = document.querySelector("img[title='Загрузить фото']");
var photobtn = document.querySelector("img[src$='bbc/photos.gif']");
photobtn.addEventListener('click',photoHandler);

function photoHandler(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById("popup-container-background").style.display='block';

}

function insertMetachars(sStartTag) {
    var
        oMsgInput = document.getElementById('message'),
        nSelStart = oMsgInput.selectionStart,
        nSelEnd = oMsgInput.selectionEnd,
        sOldText = oMsgInput.value;

    if (nSelStart > sOldText.length) {
        nSelStart = sOldText.length;
        nSelEnd = nSelStart;
    }
    if ( nSelStart < 0) {
        nSelStart=0;
        nSelEnd=0;
    }
    oMsgInput.value = sOldText.substring(0, nSelStart) +
        sStartTag +
        sOldText.substring(nSelEnd);
    oMsgInput.setSelectionRange(nSelStart + sStartTag.length,nSelStart + sStartTag.length);
    oMsgInput.focus();
}

// Вставить полученные строки в редактор
function InsertToEditor(return_files) {
    var comments=document.getElementById('comment').value;
    comments = comments ? comments : '';
    var insText='[url=' + return_files['1600'] + '][img]'+return_files['350']+'[/img]'+comments+'[/url]'+'\n';

    insertMetachars(insText);
    ClearSendFormData();
    document.getElementById("popup-container-background").style.display='none';
}

function ClearSendFormData() {

    document.getElementById('comment').value='';
    document.getElementById('file-dialog-name-id').value='';
    document.getElementById('file-dialog').value= null;

}

// Ниже идет код для вставки send_pictures.html

function sendfile() {
    // если файл не выбран
    if ( document.getElementById('file-dialog').files.length == 0 ) return;


    // данные не должны попасть в POST
    // document.getElementById('comment').setAttribute('disabled','true');
    // document.getElementById('file-dialog-name-id').setAttribute('disabled','true');
    // var formData = new FormData(document.forms.send_to_google);

    var formData = new FormData();
    var fileSelect = document.getElementById('file-dialog');
    formData.append('file',fileSelect.files[0],fileSelect.files[0].name);

    var url_ajax = 'https://woodtoolsimg.ru/resize/gcs.php';

    $.ajax({
        url: url_ajax,
        data: formData,
        processData: false,
        contentType: false,
        crossDomain : true,
        type: 'POST',
        success: function (data) {
            InsertToEditor(data);
        }
    })
    .fail(function() {
        alert( "Ошибка отправки фото на сервер" );
    });
}

function InsertContent(html) {

    document.getElementById("63096767-54D9-4EB4-B758-ECCE1FCF316D").innerHTML = html;
    document.getElementById('btnFrmGcs5').addEventListener('click', sendfile);
    document.getElementById('btnFrmGcs5_cancel').addEventListener('click', ()=>  document.getElementById("popup-container-background").style.display='none' );
    document.getElementById('5598de70-9f5a-11ea-bb37-0242ac130002').setAttribute('src',smf_theme_url + "/images/photo/comment.svg",)
}

// после загрузки photo.js выполнить код
function ready() {
    $.get(
            smf_theme_url + "/scripts/send_picture.html",
             function (data, textStatus, jqXHR) {
                 InsertContent(data);
            }
        )
        .fail(function() {
            alert( "Ошибка получения шаблона" );
        });

}

ready();
// document.addEventListener("DOMContentLoaded", ready);
