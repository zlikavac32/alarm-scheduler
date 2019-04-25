# Alarm scheduler

## Unreleased

* **[FIXED]** `NaiveAlarmScheduler` clears `started` flag on `finish()`

## 0.2.0 (2019-02-19)

* **[FIXED]** Scheduling new alarm while an other with greater timeout was scheduled from the handler
* **[NEW]** `CatchThrowableAlarmHandler` wrapper for `AlarmHandler`

## 0.1.0 (2019-02-15)

* **[NEW]** First tagged version
