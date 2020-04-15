Vue.component('list', {
    template: `
        <ul>
            <li v-for="(email,id) in emails" @click="$emit('select',selected = email)" :class="{'list-item':true,'selected':email===selected}">
                <div class="list-addresses">
                    <span class="from">{{email.from}}</span>
                    <div class="sep">&gt;</div>
                    <span class="to">{{email.to}}</span>
                </div>
                <div class="list-details">
                    <span class="subject">{{email.subject}}</span>
                    <span class="received">{{format(email.received)}}</span>
                </div>
            </li>
            <li v-if="emails.length == 0" class="empty">No mail received yet</li>
        </ul>
        `,
    props: {
        emails: {required: true}
    },
    data() {
        return {
            selected: null,
        };
    },
    methods: {
        format(s) {
            let d = new Date(s);
            let h = d.getHours() >= 10 ? String(d.getHours()) : '0'+String(d.getHours());
            let m = d.getMinutes() >= 10 ? String(d.getMinutes()) : '0'+String(d.getMinutes());
            return `${h}:${m}`;
        },
    }


});
