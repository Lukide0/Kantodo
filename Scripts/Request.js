

const CONTENT_TYPE = {
    "FORM": "application/x-www-form-urlencoded",
    "TEXT": "application/text",
    "JSON": "application/json"
};

function Request(url, method = 'GET', params = {}, sentEmpty = false, contentType = CONTENT_TYPE['FORM']) {
    return new Promise(function(resolve, reject) {

        const xhrOBJ = XHR(url, method, params, sentEmpty, contentType);
        xhrOBJ.xhr.onload = function() {
            if (this.status >= 200 && this.status < 300) {
                resolve(xhr.response);
            } else {
                reject({
                    status: this.status,
                    statusText: xhrOBJ.xh.statusText
                });
            }
        };

        xhrOBJ.xh.onerror = function () {
            reject({
              status: this.status,
              statusText: xhrOBJ.xh.statusText
            });
        };
        xhrOBJ.xh.send(xhrOBJ.params);
    })

    

}

function XHR(url, method = 'GET', params = {}, sentEmpty = false, contentType = CONTENT_TYPE['FORM']) {
    const xhr = new XMLHttpRequest();
    

    if (!sentEmpty) 
    {
        params = Object.keys(params).filter(key => params[key].length > 0).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
        }).join('&');
    } else 
    {
        params = Object.keys(params).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
        }).join('&');
    }
    xhr.open(method, url);
    xhr.setRequestHeader("Content-Type", contentType);

    return {xhr, params};
}