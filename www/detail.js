Vue.component('detail', {
    template: `
        <div class="detail-component">
            <article v-if="email">
                <header>
                    <div class="flexgroup">
                        <h2>{{email.subject}}</h2>
                        <button @click="isOpen = !isOpen">{{isOpen ? 'Hide headers' : 'Show headers' }}</button>
                    </div>
                    <ul v-show="isOpen" class="headers">
                        <li v-for="header in email.headers">
                            <span class="key">{{header.key}}</span>
                            <span class="value">{{header.value}}</span>
                         </li>
                    </ul>
                </header>
                <main>
                    <messagebody :content="{type:getContentType(),body:email.body}"></messagebody>
                </main>
            </article>
        </div>
        `,
    props: {
        email: {required: true}
    },
    data: function(){
      return {
          isOpen: false
      }
    },
    methods: {
        format(datetime) {
            let d = new Date(datetime);
            let h = d.getHours() > 10 ? String(d.getHours()) : '0' + String(d.getHours());
            let m = d.getMinutes() > 10 ? String(d.getMinutes()) : '0' + String(d.getMinutes());
            let s = d.getSeconds() > 10 ? String(d.getSeconds()) : '0' + String(d.getSeconds());
            return `${h}:${m}:${s}`;
        },
        getContentType() {
            let header = this.email.headers.filter(h=>h.key.match(/content-type/i))[0];
            return header ? header.value : 'text/plain';
        },
    },

});
