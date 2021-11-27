const Kantodo = {
    getCurrentTime() {
        let d = new Date();
        return `${d.getDay().toString().padStart(2, '0')}.${d.getMonth().toString().padStart(2, '0')}.${d.getFullYear()} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d.getSeconds().toString().padStart(2, '0')}`;
    },
    success(msg) {
        console.group(`%cSUCCESS (${this.getCurrentTime()}):`, 'color: #1faa00');
        console.log(msg);
        console.groupEnd();
    },
    info(msg) {
        console.group(`%cINFO (${this.getCurrentTime()}):`, 'color: #1976D2');
        console.log(msg);
        console.groupEnd();
    },
    warn(msg) {
        console.group(`%cWARNING (${this.getCurrentTime()}):`, 'color: #FFA000');
        console.log(msg);
        console.groupEnd();
    },
    error(msg) {
        console.group(`%cERROR (${this.getCurrentTime()}):`, 'color: #D32F2F');
        console.log(msg);
        console.groupEnd();
    }
}

export default Kantodo;