;<?PHP die(); /** @package Net_Monitor
; EDIT BELOW THIS LINE

; Services

[services]
www.toggg.com = SMTP, HTTP
www.peakepro.com = HTTP, FTP, DNS

; Alerts

; Common SMTP server default localhost, port 25, no user, no auth
[SMTP]
host = smtp.tiscali.fr

[bertrand]
SMTP = bertrand@toggg.com

[robert]
SMTP = robert@peakepro.com

; Options

[options]
state_directory = /tmp/
state_file = Net_Monitor_State_bg
subject_line = "Net_Monitor Alert"
; %h = host, %s = service, %m = message
alert_line = "%h: %s: %m, confirm you got it please"
notify_change = 1
notify_ok = 1
from_email = contact@toggg.com

; EDIT ABOVE THIS LINE
; */ ?>
