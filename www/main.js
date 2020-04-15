let app = (new Vue({
    el: '#app',
    data() {
        return {
            selectedEmail: null,
            emails: {},
        }
    },
    methods: {
        onSelect(email) {
            this.selectedEmail = email;
        }
    }
}));

let ws = (new WebSocket(window.websocketTarget));
ws.onmessage = m => {
    let messages = JSON.parse(m.data);
    for (let i in messages) {
        if (messages.hasOwnProperty(i))
            messages[i] = new Email(i, messages[i]);
    }
    app.emails = {...app.emails, ...messages};
};
