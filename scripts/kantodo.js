const Kantodo = {
    getCurrentTime() {
        let d = new Date();
        return `${(d.getDate()).toString().padStart(2, '0')}.${(d.getMonth() + 1) .toString().padStart(2, '0')}.${d.getFullYear()} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d.getSeconds().toString().padStart(2, '0')}`;
    },
    success(msg) {
        console.groupCollapsed(`%cSUCCESS (${this.getCurrentTime()})`, 'color: #1faa00');
        console.log(msg);
        console.groupEnd();

    },
    info(msg) {
        console.groupCollapsed(`%cINFO (${this.getCurrentTime()})`, 'color: #1976D2');
        console.log(msg);
        console.groupEnd();

    },
    warn(msg) {
        console.groupCollapsed(`%cWARNING (${this.getCurrentTime()})`, 'color: #FFA000');
        console.log(msg);
        console.groupEnd();

    },
    error(msg) {
        console.groupCollapsed(`%cERROR (${this.getCurrentTime()})`, 'color: #D32F2F');
        console.log(msg);
        console.groupEnd();

    }
}

export default Kantodo;