const Kantodo = {
    getCurrentTime() {
        let d = new Date();
        return `${d.getDay().toString().padStart(2, '0')}.${d.getMonth().toString().padStart(2, '0')}.${d.getFullYear()} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d.getSeconds().toString().padStart(2, '0')}`;
    },
    success(msg) {
        console.group(`%cSUCCESS (${this.getCurrentTime()}): %c${msg}`, 'color: #1faa00', 'color: #64dd17');
        console.log(msg);
        console.groupEnd();
    },
    info(msg) {
        console.group(`%cINFO (${this.getCurrentTime()}): %c${msg}`, 'color: #1976D2', 'color: #03A9F4');
        console.log(msg);
        console.groupEnd();
    },
    warn(msg) {
        console.group(`%cWARNING (${this.getCurrentTime()}): %c${msg}`, 'color: #FFA000', 'color: #FFC107');
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