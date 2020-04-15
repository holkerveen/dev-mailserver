Vue.component('messagebody', {
    template: `
        <div class="messagebody">
            <pre v-if="isContentType('text/plain')">{{content.body}}</pre>
            <div v-else-if="isContentType('text/html')" v-html="content.body"></div>
            <div v-else-if="isContentType('multipart/alternative')">
                <div class="part" v-for="(part,index) in getParts()">
                    <fieldset>
                        <legend>Part #{{index}}</legend>
                        <ul>
                            <li v-for="header in part.headers">
                                <span class="key">{{header.key}}</span>
                                <span class="value">{{header.value}}</span>
                             </li>
                         </ul>
                     </fieldset>
                    <messagebody  :content="{type:part.type,body:part.body}"></messagebody>
                </div>
            </div>
            <div v-else>** Unsupported content type {{content.type}}**</div>
        </div>
        `,
    props: {
        content: {required: true}
    },
    methods: {
        isContentType(type) {
            let detected = this.content.type.split(';')[0];
            return type.toLowerCase().trim() === detected.toLowerCase().trim();
        },
        getParts() {
            let boundary = '--'
                + this.content.type
                    .split(';')
                    .filter(split => /^\s*boundary\s*=/i.test(split))[0]
                    .split('=').slice(1).join('=')
                    .trim();
            return this.content.body
                .split(boundary)
                .slice(1, -1)
                .map(p=>p.replace(/^\n|\n$/g,''))
                .map(part=>{
                    let headers = part
                        .split('\n\n',2)[0]
                        .split('\n')
                        .map(h=>{
                            let r = h.split(':');
                            return {key: r[0], value: r.slice(1).join(':').trim()};
                        })
                        .map(h => {return {key:h.key, value: h.value.trim()}});
                    let body = part.split('\n\n').slice(1).join('\n\n');
                    let type = headers.filter(h=>h.key.match(/content-type/i))[0];
                    type = type ? type.value : 'text/plain';
                    return {
                        'headers': headers,
                        'body': body,
                        'type': type
                    };
                });
        },
    },

});
