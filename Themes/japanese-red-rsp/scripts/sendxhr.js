    function FormData1() {

        let boundary = "--------FormData" + Math.random();
        let body = "";

            body += "--" + boundary + "\r\n";
            // file upload
            // if (field[1].name) {
        let file = field[1];
        body += "Content-Disposition: form-data; name=\""+ 'file' +"\"; filename=\""+ 'send.jpg' +"\"\r\n";
        body += "Content-Type: "+ file.type +"\r\n\r\n";
        body += file.getAsBinary() + "\r\n";

        body += "--" + boundary +"--";
        return body;
    }
