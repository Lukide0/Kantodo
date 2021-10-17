const Kantodo = {
    getCurrentTime() {
        let d = new Date();
        return `${d.getDay().toString().padStart(2, '0')}.${d.getMonth().toString().padStart(2, '0')}.${d.getFullYear()} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d.getSeconds().toString().padStart(2, '0')}`;
    },
    success(msg) {
        console.log(`%cSUCCESS (${this.getCurrentTime()}): %c${msg}`, 'color: #1faa00', 'color: #64dd17');
    },
    info(msg) {
        console.log(`%cINFO (${this.getCurrentTime()}): %c${msg}`, 'color: #1976D2', 'color: #03A9F4');
    },
    warn(msg) {
        console.log(`%cWARNING (${this.getCurrentTime()}): %c${msg}`, 'color: #FFA000', 'color: #FFC107');
    },
    error(msg) {
        console.log(`%cERROR (${this.getCurrentTime()}): %c${msg}`, 'color: #D32F2F', 'color: #F44336');
    }
}

export default Kantodo;