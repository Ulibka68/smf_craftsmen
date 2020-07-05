const resize_url = 'https://woodtoolsimg.ru//resize/gcs.php';

// let photobtn = document.querySelector("img[title='Загрузить фото']");
let photobtn = document.querySelector("img[src$='bbc/photos.gif']");
photobtn.addEventListener('click',photoHandler);

function photoHandler(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById("popup-container-background").style.display='block';

}

function insertMetachars(sStartTag) {
    let
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
    let comments=document.getElementById('comment').value;
    comments = comments ? comments : '';
    let insText='[url=' + return_files['1600'] + '][img]'+return_files['350']+'[/img]'+comments+'[/url]'+'\n';

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
    document.getElementById('comment').setAttribute('disabled','true');
    document.getElementById('file-dialog-name-id').setAttribute('disabled','true');

    let formData = new FormData(document.forms.send_to_google);


    fetch(resize_url, {
            method: 'POST',
            body: formData
        }
    )
        // сервер вернул ответ 200 ?
        .then((res) => {
            if (res.status >= 200 && res.status < 300) {
                return res;
            } else {
                let error = new Error(res.statusText);
                error.response = res;
                throw error
            }
        })
        .then(r => r.json())
        .then(return_files => {
            // обработка и вставка информации на форум
            InsertToEditor(return_files);

        })
        .catch((e) => {
            console.log('Error: ' + e.message);
            console.log(e.response);
        });

}

function InsertContent(html) {

    document.getElementById("63096767-54D9-4EB4-B758-ECCE1FCF316D").innerHTML = html;
    document.getElementById('btnFrmGcs5').addEventListener('click', sendfile);
    document.getElementById('btnFrmGcs5_cancel').addEventListener('click', ()=>  document.getElementById("popup-container-background").style.display='none' );

}

// после загрузки photo.js выполнить код
function ready() {

    fetch(smf_theme_url + "/scripts/send_picture.html", {method: 'GET'})
        // сервер вернул ответ 200 ?
        .then((res) => {
            if (res.status >= 200 && res.status < 300) {
                return res;
            } else {
                let error = new Error(res.statusText);
                error.response = res;
                throw error
            }
        })
        .then( data => data.text())
        .then(html => {
                InsertContent(html);
            }
        );

}

ready();
// document.addEventListener("DOMContentLoaded", ready);
