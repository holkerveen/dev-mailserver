class Email {
    index = null;
    headers = [];
    body = null;
    from = null;
    received = new Date();

    constructor(index, data) {
        this.index = index;
        this.headers = data.headers;
        this.from = data.from;
        this.received = data.received;
        this.body = data.body;
    }

    header(name) {
        let header = this.headers.filter(h => h.key.toLowerCase() === name.toLowerCase());
        return header[0] ? header[0].value : null;
    }

    get to() {
        return this.header('to');
    }

    get subject() {
        return this.header('subject');
    }
}