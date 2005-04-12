;<?PHP die(); /** @package Net_Monitor
; EDIT BELOW THIS LINE

; Services

[services]
www.toggg.com = SMTP, HTTP
www.peakepro.com = HTTP, FTP, DNS

; Alerts

; Common SMS server: clickatell
[SMS]
SMS_provider = clickatell_http
username = pique
password = robert
api_id = x.y.z

; Extra SMS server: sms2email
[SMS1]
SMS_provider = sms2email_http
username = gruyere
password = belletranche

; Extra SMS server: Vodafone Italy
[SMS2]
SMS_provider = vodafoneitaly_smtp
username = italiano

[bertrand]
SMS = 1234567890

[robert]
SMS1 = 2345678901

; Options

[options]
state_directory = /tmp/
state_file = Net_Monitor_State_bg
subject_line = "Net_Monitor Alert"
; %h = host, %s = service, %m = message
alert_line = "%h: %s: %m, confirm you got it please"
sms_line = "%h:%s->%c"
notify_change = 1
notify_ok = 1
from_email = contact@toggg.com

; EDIT ABOVE THIS LINE
; */ ?>
