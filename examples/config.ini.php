;<?PHP die(); /** @package Net_Monitor
; EDIT BELOW THIS LINE

; Services

[services]
foo.example.com = SMTP, DNS
bar.example.com = HTTP, FTP, DNS

; Alerts
; Common SMTP server default localhost, port 25, no user, no auth
; (the SMTP_default option array)
[SMTP]
host = smtp.example.com
port = 25
auth = true
username = user2
password = supersecret

; Extra SMTP server(s) for alert about default one, host is the key
[SMTP_Super]
host = smtp.extra.com
port = 25
auth = true
username = user2'
password = supersecret

; Common SMS server (the SMS_default option array)
[SMS]
SMS_provider = clickatell_http
username = SMS_Subscriber
password = supersecret
api_id = x.y.z
; optionaly some extra SMS servers, SMS_provider is the key

[commonUser]
SMTP = common@example.com

[extraUser]
SMTP_Super = safety@demo.com
SMS = 0123456789012

; Options

[options]
state_directory = /tmp/
state_file = Net_Monitor_State
subject_line = "Net_Monitor Alert"
; %h = host, %s = service, %m = message
alert_line = "%h: %s: %m"
notify_change = 1
notify_ok = 1
from_email = noreply@example.com
smtp_debug = false
sms_from = '0123456789'
sms_debug = false

; EDIT ABOVE THIS LINE
; */ ?>
