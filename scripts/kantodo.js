const Kantodo = {
    __last: null,
    getCurrentTime() {
        let d = new Date();
        return `${(d.getDate()).toString().padStart(2, '0')}.${(d.getMonth() + 1) .toString().padStart(2, '0')}.${d.getFullYear()} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d.getSeconds().toString().padStart(2, '0')}`;
    },
    success(msg) {
        if (this.__last != 'success') 
        {
            console.groupEnd();
            this.__last = 'success';
            console.groupCollapsed(`%cSUCCESS:`, 'color: #1faa00');
        }
        console.groupCollapsed(`%c(${this.getCurrentTime()})`, 'color: #1faa00');
        console.log(msg);
        console.groupEnd();

    },
    info(msg) {
        if (this.__last != 'info') 
        {
            console.groupEnd();
            this.__last = 'info';
            console.groupCollapsed(`%cINFO:`, 'color: #1976D2');
        }
        console.groupCollapsed(`%c(${this.getCurrentTime()})`, 'color: #1976D2');
        console.log(msg);
        console.groupEnd();

    },
    warn(msg) {
        if (this.__last != 'warn') 
        {
            console.groupEnd();
            this.__last = 'warn';
            console.groupCollapsed(`%cWARNING:`, 'color: #FFA000');
        }
        console.groupCollapsed(`%c(${this.getCurrentTime()})`, 'color: #FFA000');
        console.log(msg);
        console.groupEnd();

    },
    error(msg) {
        if (this.__last != 'error') 
        {
            console.groupEnd();
            this.__last = 'error';
            console.groupCollapsed(`%cERROR:`, 'color: #D32F2F');
        }
        console.groupCollapsed(`%c(${this.getCurrentTime()})`, 'color: #D32F2F');
        console.log(msg);
        console.groupEnd();

    }
}

export default Kantodo;