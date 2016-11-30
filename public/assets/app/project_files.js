var ProjectFiles = function (options) {
    this.fileEditor = ace.edit(options.fileEditor);
    this.modal = $(options.modal);
    this.container = $(options.container);
    this.files = options.files;
    this.template = Handlebars.compile($(options.template).html());
    this.render();
};

ProjectFiles.prototype.render = function () {
    $('tbody', this.container).html(this.template({
        files: _.sortBy(this.files, 'name')
    }));
};

ProjectFiles.prototype.removeFile = function (name) {
    this.files = _.reject(this.files, function (item) {
        return item.name == name;
    });
    this.render();
};

ProjectFiles.prototype.openNewFileWindow = function () {
    this.resetModalForm();
    this.modal.modal('show');
    setTimeout(function () {
        this.fileEditor.getSession().setValue('');
    }.bind(this), 300);
};

ProjectFiles.prototype.openEditFileWindow = function (name) {
    $('input[name=old_name]', this.modal).val(name);
    var file = _.findWhere(this.files, {name: name});
    $('input[name=name]', this.modal).val(file.name);
    $(this.modal).modal('show');
    setTimeout(function () {
        this.fileEditor.getSession().setValue(file.content);
    }.bind(this), 300);
};

ProjectFiles.prototype.save = function () {
    var oldName = $('input[name=old_name]', this.modal).val();
    var name = $('input[name=name]', this.modal).val();
    var content = this.fileEditor.getSession().getValue();
    if (oldName.length == 0 || (oldName != name)) {
        var oldFile = _.findWhere(this.files, {name: name});
        if (oldFile != undefined) {
            if (confirm('Aynı isimli başka bir dosya var. Üzerine yazılsın mı?')) {
                this.updateFileContent(name, content, oldName);
            }
        } else {
            this.updateFileContent(name, content, oldName);
        }
    } else {
        this.updateFileContent(name, content, oldName);
    }
};

ProjectFiles.prototype.updateFileContent = function (name, content, oldName) {
    this.files = _.reject(this.files, function (file) {
        return file.name == name || file.name == oldName;
    });
    this.files.push({
        name: name,
        content: content,
        notSaved: true
    });
    this.resetModalForm();
    this.fileEditor.getSession().setValue('');
    this.modal.modal('hide');
    this.render();
};

ProjectFiles.prototype.closeModal = function () {
    this.modal.modal('hide');
};

ProjectFiles.prototype.resetModalForm = function () {
    $('input[name=old_name]', this.modal).val('');
    $('input[name=name]', this.modal).val('');
};
