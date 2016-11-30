var ProjectNotifications = function (options) {
    this.modal = $(options.modal);
    this.container = $(options.container);
    this.notifications = options.notifications;
    this.template = Handlebars.compile($(options.template).html());
    this.render();
};

ProjectNotifications.prototype.render = function () {
    $('tbody', this.container).html(this.template({
        notifications: _.sortBy(this.notifications, 'name')
    }));
};

ProjectNotifications.prototype.removeNotification = function (name) {
    this.notifications = _.reject(this.notifications, function (item) {
        return item.name == name;
    });
    this.render();
};

ProjectNotifications.prototype.openNewNotificationWindow = function () {
    this.resetModalForm();
    this.modal.modal('show');
};

ProjectNotifications.prototype.openEditNotificationWindow = function (name) {
    this.resetModalForm();
    $('input[name=old_name]', this.modal).val(name);
    var notification = _.findWhere(this.notifications, {name: name});
    $('input[name=name]', this.modal).val(notification.name);
    $('input[name=url]', this.modal).val(notification.data.url);
    this.updateAcceptedActionsSelectBox(notification.data.accepted_actions);
    $(this.modal).modal('show');
};

ProjectNotifications.prototype.save = function () {
    var oldName = $('input[name=old_name]', this.modal).val();
    var name = $('input[name=name]', this.modal).val();
    var url = $('input[name=url]', this.modal).val();
    var acceptedActions = $('select[name=accepted_actions]', this.modal).val();
    var context = {
        type: 'slack',
        name: name,
        data: {
            url: url,
            accepted_actions: acceptedActions
        }
    };
    if (oldName.length == 0 || (oldName != name)) {
        var oldNotification = _.findWhere(this.notifications, {name: name});
        if (oldNotification != undefined) {
            if (confirm('Aynı isimli başka bir bildirim var. Üzerine yazılsın mı?')) {
                this.updateNotificationContent(oldName, context);
            }
        } else {
            this.updateNotificationContent(oldName, context);
        }
    } else {
        this.updateNotificationContent(oldName, context);
    }
};

ProjectNotifications.prototype.updateNotificationContent = function (oldName, context) {
    this.notifications = _.reject(this.notifications, function (notification) {
        return notification.name == context.name || notification.name == oldName;
    });
    context.notSaved = true;
    this.notifications.push(context);
    this.resetModalForm();
    this.modal.modal('hide');
    this.render();
};

ProjectNotifications.prototype.closeModal = function () {
    this.modal.modal('hide');
};

ProjectNotifications.prototype.resetModalForm = function () {
    $('input[name=old_name]', this.modal).val('');
    $('input[name=name]', this.modal).val('');
    $('input[name=url]', this.modal).val('');
    this.updateAcceptedActionsSelectBox('');
};

ProjectNotifications.prototype.updateAcceptedActionsSelectBox = function (value) {
    $('select[name=accepted_actions]', this.modal).val(value);
    $('select[name=accepted_actions]', this.modal).select2({
        theme: 'classic',
        data: [
            {id: 'project_setup_starting', text: 'Kurulum Başlatılıyor'},
            {id: 'project_setup_finished', text: 'Kurulum Tamamlandı'},
            {id: 'project_setup_failed', text: 'Kurulum Sorunlu'},
            {id: 'project_starting', text: 'Proje Başlatılıyor'},
            {id: 'project_started', text: 'Proje Başlatıldı'},
            {id: 'project_start_failed', text: 'Proje Başlatışması Sorunlu'},
            {id: 'project_stopping', text: 'Proje Durduruluyor'},
            {id: 'project_stopped', text: 'Proje Durduruldu'},
            {id: 'project_stop_failed', text: 'Proje Durdurulması Sorunlu'}
        ]
    });
};