const CONTENT_TYPE = {
    'FORM': 'application/x-www-form-urlencoded',
    'TEXT': 'application/text',
    'JSON': 'application/json'
};

function Request(url, method = 'GET', params = {}, sentEmpty = false, contentType = CONTENT_TYPE['FORM']) {
    return new Promise(function(resolve, reject) {

        const xhrOBJ = XHR(url, method, params, sentEmpty, contentType);
        xhrOBJ.xhr.onload = function() {
            if (this.status >= 200 && this.status < 300) {
                try {
                    resolve(JSON.parse(xhrOBJ.xhr.response));
                } catch (error) {
                    reject({
                        status: xhrOBJ.xhr.status,
                        statusText: "JSON ERROR",
                        response: xhrOBJ.xhr.responseText,
                        error: ""
                    });
                }
            } else {
                reject({
                    status: xhrOBJ.xhr.status,
                    statusText: xhrOBJ.xhr.statusText,
                    response: xhrOBJ.xhr.responseText,
                    error: JSON.parse(xhrOBJ.xhr.response).error
                });
            }
        };

        xhrOBJ.xhr.onerror = function () {
            if (this.status == 401)
                window.location.reload(true);

            reject({
              status: this.status,
              statusText: xhrOBJ.xhr.statusText
            });
        };
        xhrOBJ.xhr.send(xhrOBJ.params);
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
    xhr.setRequestHeader('Content-Type', contentType);

    return {xhr, params};
}

export {Request, XHR, CONTENT_TYPE};