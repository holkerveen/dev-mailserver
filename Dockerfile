FROM alpine:latest

RUN apk add --no-cache \
	bash \
	ca-certificates \
	libsasl \
	mailx \
	postfix \
	rsyslog \
	runit \
	php7 \
	php7-json \
	php7-sockets \
	php7-pcntl \
	inotify-tools

COPY runit_bootstrap /usr/sbin/runit_bootstrap
COPY rsyslog.conf /etc/rsyslog.conf
COPY www /www
COPY src /src
COPY stream.php /

RUN chmod 755 /usr/sbin/runit_bootstrap \
 \
 && mkdir -p /etc/service/php \
 && printf "#!/bin/sh\nset -e\n\nexec /usr/bin/php -S 0.0.0.0:80 -t /www\n" > /etc/service/php/run \
 && chmod 755 /etc/service/php/run \
 \
 && mkdir -p /etc/service/rsyslog \
 && printf "#!/bin/sh\nset -e\n\nexec rsyslogd -n\n"> /etc/service/rsyslog/run \
 && chmod 755 /etc/service/rsyslog/run \
 \
 && mkdir -p /etc/service/postfix \
 && printf '#!/bin/bash\nset -e\nnewaliases\n/usr/libexec/postfix/master -c /etc/postfix -d 2>&1\n' > /etc/service/postfix/run \
 && chmod 755 /etc/service/postfix/run \
 && printf 'local_recipient_maps=\n' >> /etc/postfix/main.cf \
 && printf 'luser_relay = root\n' >> /etc/postfix/main.cf \
 && printf 'mydestination = regexp:/etc/postfix/match_all_destination_re\n' >> /etc/postfix/main.cf \
 && printf 'smtputf8_enable = no\n' >> /etc/postfix/main.cf \
 && printf 'inet_interfaces = all\n' >> /etc/postfix/main.cf \
 && printf 'inet_protocols = ipv4\n' >> /etc/postfix/main.cf \
 && printf 'root: henk\n' >> /etc/aliases \
 && printf '// result_is_ignored\n' > /etc/postfix/match_all_destination_re \
 \
 && mkdir -p /etc/service/ws \
 && printf "#!/bin/sh\nset -e\n\nexec /usr/bin/php /stream.php\n" > /etc/service/ws/run \
 && chmod 755 /etc/service/ws/run

RUN ln -sf /dev/stdout /var/log/mail.log

STOPSIGNAL SIGKILL

CMD ["/usr/sbin/runit_bootstrap"]

