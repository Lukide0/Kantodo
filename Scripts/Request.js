

const CONTENT_TYPE = {
    "FORM": "application/x-www-form-urlencoded",
    "TEXT": "application/text",
    "JSON": "application/json"
};

function Request(url, method = 'GET', params = {}, contentType = CONTENT_TYPE['FORM']) {
    return new Promise(function(resolve, reject) {
        const xhr = new XMLHttpRequest();
    


        params = Object.keys(params).filter(key => params[key].length > 0).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
        }).join('&');
        xhr.open(method, url);
        xhr.setRequestHeader("Content-Type", contentType);
        xhr.onload = function() {
            if (this.status >= 200 && this.status < 300) {
                resolve(xhr.response);
            } else {
                reject({
                    status: this.status,
                    statusText: xhr.statusText
                });
            }
        };

        xhr.onerror = function () {
            reject({
              status: this.status,
              statusText: xhr.statusText
            });
        };
        xhr.send(params);
    })

    

}