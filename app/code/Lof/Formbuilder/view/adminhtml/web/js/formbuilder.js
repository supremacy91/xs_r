define([
    'jquery',
    'jquery/ui',
    'Lof_Formbuilder/js/underscore',
    'Lof_Formbuilder/js/rivets',
    'Lof_Formbuilder/js/backbone',
    'Lof_Formbuilder/js/deep-model',
    'Lof_Formbuilder/js/scrolltofixed',
], function ($, ui, Underscore, rivets, Backbone) {

    var currentModel = '';

    jQuery.scrollWindowTo = function (pos, duration, cb) {
        if (duration == null) {
            duration = 0;
        }
        if (pos === $(window).scrollTop()) {
            $(window).trigger('scroll');
            if (typeof cb === "function") {
                cb();
            }
            return;
        }
        return $('html, body').animate({
            scrollTop: pos - 150
        }, 600);
    };

    (function () {
        rivets.binders.input = {
            publishes: true,
            routine: rivets.binders.value.routine,
            bind: function (el) {
                return jQuery(el).bind('input.rivets', this.publish);
            },
            unbind: function (el) {
                return jQuery(el).unbind('input.rivets');
            }
        };

        rivets.configure({
            prefix: "rv",
            adapter: {
                subscribe: function (obj, keypath, callback) {
                    callback.wrapped = function (m, v) {
                        return callback(v);
                    };
                    return obj.on('change:' + keypath, callback.wrapped);
                },
                unsubscribe: function (obj, keypath, callback) {
                    return obj.off('change:' + keypath, callback.wrapped);
                },
                read: function (obj, keypath) {
                    if (keypath === "cid") {
                        return obj.cid;
                    }
                    return obj.get(keypath);
                },
                publish: function (obj, keypath, value) {
                    if (obj.cid) {
                        return obj.set(keypath, value);
                    } else {
                        return obj[keypath] = value;
                    }
                }
            }
        });

    }).call(this);

    (function () {
        var BuilderView, EditFieldView, Formbuilder, FormbuilderCollection, FormbuilderModel, ViewFieldView, _ref,
            _ref1, _ref2, _ref3, _ref4,
            __hasProp = {}.hasOwnProperty,
            __extends = function (child, parent) {
                for (var key in parent) {
                    if (__hasProp.call(parent, key)) child[key] = parent[key];
                }

                function ctor() {
                    this.constructor = child;
                }

                ctor.prototype = parent.prototype;
                child.prototype = new ctor();
                child.__super__ = parent.prototype;
                return child;
            };

        FormbuilderModel = (function (_super) {
            __extends(FormbuilderModel, _super);

            function FormbuilderModel() {
                _ref = FormbuilderModel.__super__.constructor.apply(this, arguments);
                return _ref;
            }

            FormbuilderModel.prototype.sync = function () {
            };

            FormbuilderModel.prototype.indexInDOM = function () {
                var $wrapper,
                    _this = this;
                $wrapper = jQuery(".fb-field-wrapper").filter((function (_, el) {
                    return jQuery(el).data('cid') === _this.cid;
                }));
                return jQuery(".fb-field-wrapper").index($wrapper);
            };

            FormbuilderModel.prototype.is_input = function () {
                return Formbuilder.inputFields[this.get(Formbuilder.options.mappings.FIELD_TYPE)] != null;
            };

            return FormbuilderModel;

        })(Backbone.DeepModel);

        FormbuilderCollection = (function (_super) {
            __extends(FormbuilderCollection, _super);

            function FormbuilderCollection() {
                _ref1 = FormbuilderCollection.__super__.constructor.apply(this, arguments);
                return _ref1;
            }

            FormbuilderCollection.prototype.initialize = function () {
                return this.on('add', this.copyCidToModel);
            };

            FormbuilderCollection.prototype.model = FormbuilderModel;

            FormbuilderCollection.prototype.comparator = function (model) {
                return model.indexInDOM();
            };

            FormbuilderCollection.prototype.copyCidToModel = function (model) {
                return model.attributes.cid = model.cid;
            };

            return FormbuilderCollection;

        })(Backbone.Collection);

        ViewFieldView = (function (_super) {
            __extends(ViewFieldView, _super);

            function ViewFieldView() {
                _ref2 = ViewFieldView.__super__.constructor.apply(this, arguments);
                return _ref2;
            }

            ViewFieldView.prototype.className = "fb-field-wrapper";

            ViewFieldView.prototype.events = {
                'click .subtemplate-wrapper': 'focusEditView',
                'click .js-duplicate': 'duplicate',
                'click .js-clear': 'clear'
            };

            ViewFieldView.prototype.initialize = function (options) {
                this.parentView = options.parentView;
                this.listenTo(this.model, "change", this.render);
                return this.listenTo(this.model, "destroy", this.remove);
            };
            ViewFieldView.prototype.render = function () {
                var regEx = /^col-sm/;
                var elm = $("div");

                var classes = this.$el.attr('class').split(/\s+/); //it will return  foo1, foo2, foo3, foo4
                for (var i = 0; i < classes.length; i++) {
                    var className = classes[i];
                    if (className.match(regEx)) {
                        this.$el.removeClass(className);
                    }
                }

                this.$el.addClass('response-field-' + this.model.get(Formbuilder.options.mappings.FIELD_TYPE) + ' col-sm-' + this.model.get(Formbuilder.options.mappings.WRAPPERCOL)).data('cid', this.model.cid).html(Formbuilder.templates["view/base" + (!this.model.is_input() ? '_non_input' : '')]({
                    rf: this.model
                }));
                return this;
            };

            ViewFieldView.prototype.focusEditView = function () {
                return this.parentView.createAndShowEditView(this.model);
            };

            ViewFieldView.prototype.clear = function (e) {
                var cb, x,
                    _this = this;
                e.preventDefault();
                e.stopPropagation();
                cb = function () {
                    _this.parentView.handleFormUpdate();
                    return _this.model.destroy();
                };
                x = Formbuilder.options.CLEAR_FIELD_CONFIRM;
                switch (typeof x) {
                    case 'string':
                        if (confirm(x)) {
                            return cb();
                        }
                        break;
                    case 'function':
                        return x(cb);
                    default:
                        return cb();
                }
            };

            ViewFieldView.prototype.duplicate = function () {
                var attrs;
                attrs = _.clone(this.model.attributes);
                delete attrs['id'];
                attrs['label'] += ' Copy';
                return this.parentView.createField(attrs, {
                    position: this.model.indexInDOM() + 1
                });
            };

            return ViewFieldView;

        })(Backbone.View);

        EditFieldView = (function (_super) {
            __extends(EditFieldView, _super);

            function EditFieldView() {
                _ref3 = EditFieldView.__super__.constructor.apply(this, arguments);
                return _ref3;
            }

            EditFieldView.prototype.className = "edit-response-field";

            EditFieldView.prototype.events = {
                'click .js-add-option': 'addOption',
                'click .js-remove-option': 'removeOption',
                'click .js-add-cate-option': 'addCategoryOption',
                'click .js-remove-cate-option': 'removeCategoryOption',
                'click .js-default-updated': 'defaultUpdated',
                'input .option-label-input': 'forceRender'
            };

            EditFieldView.prototype.initialize = function (options) {
                this.parentView = options.parentView;
                return this.listenTo(this.model, "destroy", this.remove);
            };

            EditFieldView.prototype.render = function () {
                this.$el.html(Formbuilder.templates["edit/base" + (!this.model.is_input() ? '_non_input' : '')]({
                    rf: this.model
                }));
                rivets.bind(this.$el, {
                    model: this.model
                });
                return this;
            };

            EditFieldView.prototype.remove = function () {
                this.parentView.editView = void 0;
                this.parentView.$el.find("[data-target=\"#addField\"]").click();
                return EditFieldView.__super__.remove.apply(this, arguments);
            };

            EditFieldView.prototype.addOption = function (e) {
                var $el, i, newOption, options;
                $el = jQuery(e.currentTarget);
                i = this.$el.find('.option').index($el.closest('.option'));
                options = this.model.get(Formbuilder.options.mappings.OPTIONS) || [];
                newOption = {
                    label: "",
                    checked: false
                };
                if (i > -1) {
                    options.splice(i + 1, 0, newOption);
                } else {
                    options.push(newOption);
                }
                this.model.set(Formbuilder.options.mappings.OPTIONS, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.OPTIONS);
                return this.forceRender();
            };

            EditFieldView.prototype.removeOption = function (e) {
                var $el, index, options;
                $el = jQuery(e.currentTarget);
                index = this.$el.find(".js-remove-option").index($el);
                options = this.model.get(Formbuilder.options.mappings.OPTIONS);
                options.splice(index, 1);
                this.model.set(Formbuilder.options.mappings.OPTIONS, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.OPTIONS);
                return this.forceRender();
            };

            EditFieldView.prototype.addCategoryOption = function (e) {
                var $el, i, newOption, options;
                $el = jQuery(e.currentTarget);
                i = this.$el.find('.option').index($el.closest('.option'));
                options = this.model.get(Formbuilder.options.mappings.CATE_ID) || [];
                newOption = {
                    value: "",
                    label: "",
                    checked: false
                };
                if (i > -1) {
                    options.splice(i + 1, 0, newOption);
                } else {
                    options.push(newOption);
                }
                this.model.set(Formbuilder.options.mappings.CATE_ID, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.CATE_ID);
                return this.forceRender();
            };

            EditFieldView.prototype.removeCategoryOption = function (e) {
                var $el, index, options;
                $el = jQuery(e.currentTarget);
                index = this.$el.find(".js-remove-option").index($el);
                options = this.model.get(Formbuilder.options.mappings.CATE_ID);
                options.splice(index, 1);
                this.model.set(Formbuilder.options.mappings.CATE_ID, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.CATE_ID);
                return this.forceRender();
            };

            EditFieldView.prototype.defaultUpdated = function (e) {
                var $el;
                $el = jQuery(e.currentTarget);
                if (this.model.get(Formbuilder.options.mappings.FIELD_TYPE) !== 'checkboxes') {
                    this.$el.find(".js-default-updated").not($el).attr('checked', false).trigger('change');
                }
                return this.forceRender();
            };

            EditFieldView.prototype.forceRender = function () {
                return this.model.trigger('change');
            };

            return EditFieldView;

        })(Backbone.View);

        BuilderView = (function (_super) {
            __extends(BuilderView, _super);

            function BuilderView() {
                _ref4 = BuilderView.__super__.constructor.apply(this, arguments);
                return _ref4;
            }

            BuilderView.prototype.SUBVIEWS = [];

            BuilderView.prototype.events = {
                'click .js-save-form': 'saveForm',
                'click .fb-tabs a': 'showTab',
                'click .fb-add-field-types a': 'addField',
                'mouseover .fb-add-field-types': 'lockLeftWrapper',
                'mouseout .fb-add-field-types': 'unlockLeftWrapper'
            };

            BuilderView.prototype.initialize = function (options) {
                var selector;
                selector = options.selector, this.formBuilder = options.formBuilder, this.bootstrapData = options.bootstrapData;
                if (selector != null) {
                    this.setElement(jQuery(selector));
                }
                this.collection = new FormbuilderCollection;
                this.collection.bind('add', this.addOne, this);
                this.collection.bind('reset', this.reset, this);
                this.collection.bind('change', this.handleFormUpdate, this);
                this.collection.bind('destroy add reset', this.hideShowNoResponseFields, this);
                this.collection.bind('destroy', this.ensureEditViewScrolled, this);
                this.render();
                this.collection.reset(this.bootstrapData);
                return this.bindSaveEvent();
            };

            BuilderView.prototype.bindSaveEvent = function () {
                var _this = this;
                this.formSaved = true;
                this.saveFormButton = this.$el.find(".js-save-form");
                this.saveFormButton.attr('disabled', true).text(Formbuilder.options.dict.ALL_CHANGES_SAVED);
                if (!!Formbuilder.options.AUTOSAVE) {
                    setInterval(function () {
                        return _this.saveForm.call(_this);
                    }, 500);
                }
                return jQuery(window).bind('beforeunload', function () {
                    if (_this.formSaved) {
                        return void 0;
                    } else {
                        return Formbuilder.options.dict.UNSAVED_CHANGES;
                    }
                });
            };

            BuilderView.prototype.reset = function () {
                this.$responseFields.html('');
                return this.addAll();
            };

            BuilderView.prototype.render = function () {
                var subview, _i, _len, _ref5;
                this.$el.html(Formbuilder.templates['page']());
                this.$fbLeft = this.$el.find('.fb-left');
                this.$responseFields = this.$el.find('.fb-response-fields');
                this.bindWindowScrollEvent();
                this.hideShowNoResponseFields();
                _ref5 = this.SUBVIEWS;
                for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
                    subview = _ref5[_i];
                    new subview({
                        parentView: this
                    }).render();
                }
                return this;
            };

            BuilderView.prototype.bindWindowScrollEvent = function () {
            };

            BuilderView.prototype.showTab = function (e) {
                var $el, first_model, target;
                $el = jQuery(e.currentTarget);
                target = $el.data('target');
                $el.closest('li').addClass('active').siblings('li').removeClass('active');
                jQuery(target).addClass('active').siblings('.fb-tab-pane').removeClass('active');
                if (target !== '#editField') {
                    this.unlockLeftWrapper();
                }
                if (target === '#editField' && !this.editView && (first_model = this.collection.models[0])) {
                    currentModel = first_model;
                    return this.createAndShowEditView(first_model);
                }
            };

            BuilderView.prototype.addOne = function (responseField, _, options) {
                var $replacePosition, view;
                view = new ViewFieldView({
                    model: responseField,
                    parentView: this
                });
                if (options.$replaceEl != null) {
                    return options.$replaceEl.replaceWith(view.render().el);
                } else if ((options.position == null) || options.position === -1) {
                    return this.$responseFields.append(view.render().el);
                } else if (options.position === 0) {
                    return this.$responseFields.prepend(view.render().el);
                } else if (($replacePosition = this.$responseFields.find(".fb-field-wrapper").eq(options.position))[0]) {
                    return $replacePosition.before(view.render().el);
                } else {
                    return this.$responseFields.append(view.render().el);
                }
            };

            BuilderView.prototype.setSortable = function () {
                var _this = this;
                if (this.$responseFields.hasClass('ui-sortable')) {
                    this.$responseFields.sortable('destroy');
                }
                this.$responseFields.sortable({
                    forcePlaceholderSize: true,
                    placeholder: 'sortable-placeholder',
                    stop: function (e, ui) {
                        var rf;
                        if (ui.item.data('field-type')) {
                            rf = _this.collection.create(Formbuilder.helpers.defaultFieldAttrs(ui.item.data('field-type')), {
                                $replaceEl: ui.item
                            });
                            _this.createAndShowEditView(rf);
                        }
                        _this.handleFormUpdate();
                        return true;
                    },
                    update: function (e, ui) {
                        if (!ui.item.data('field-type')) {
                            return _this.ensureEditViewScrolled();
                        }
                    }
                });
                return this.setDraggable();
            };

            BuilderView.prototype.setDraggable = function () {
                var $addFieldButtons,
                    _this = this;
                $addFieldButtons = this.$el.find("[data-field-type]");
                return $addFieldButtons.draggable({
                    connectToSortable: this.$responseFields,
                    helper: function () {
                        var $helper;
                        $helper = jQuery("<div class='response-field-draggable-helper' />");
                        $helper.css({
                            width: _this.$responseFields.width(),
                            height: '80px'
                        });
                        return $helper;
                    }
                });
            };

            BuilderView.prototype.addAll = function () {
                this.collection.each(this.addOne, this);
                return this.setSortable();
            };

            BuilderView.prototype.hideShowNoResponseFields = function () {
                return this.$el.find(".fb-no-response-fields")[this.collection.length > 0 ? 'hide' : 'show']();
            };

            BuilderView.prototype.addField = function (e) {
                var field_type;
                field_type = jQuery(e.currentTarget).data('field-type');
                return this.createField(Formbuilder.helpers.defaultFieldAttrs(field_type));
            };

            BuilderView.prototype.createField = function (attrs, options) {
                var rf;
                rf = this.collection.create(attrs, options);
                this.createAndShowEditView(rf);
                return this.handleFormUpdate();
            };

            BuilderView.prototype.createAndShowEditView = function (model) {
                var $newEditEl, $responseFieldEl;
                $responseFieldEl = this.$el.find(".fb-field-wrapper").filter(function () {
                    return jQuery(this).data('cid') === model.cid;
                });
                $responseFieldEl.addClass('editing').siblings('.fb-field-wrapper').removeClass('editing');
                if (this.editView) {
                    currentModel = model;
                    if (this.editView.model.cid === model.cid) {
                        this.$el.find(".fb-tabs a[data-target=\"#editField\"]").click();
                        this.scrollLeftWrapper($responseFieldEl);
                        return;
                    }
                    this.editView.remove();
                }
                this.editView = new EditFieldView({
                    model: model,
                    parentView: this
                });
                $newEditEl = this.editView.render().$el;
                this.$el.find(".fb-edit-field-wrapper").html($newEditEl);
                this.$el.find(".fb-tabs a[data-target=\"#editField\"]").click();
                this.scrollLeftWrapper($responseFieldEl);
                return this;
            };

            BuilderView.prototype.ensureEditViewScrolled = function () {
                if (!this.editView) {
                    return;
                }
                return this.scrollLeftWrapper(jQuery(".fb-field-wrapper.editing"));
            };

            BuilderView.prototype.scrollLeftWrapper = function ($responseFieldEl) {
                var _this = this;
                this.unlockLeftWrapper();
                if (!$responseFieldEl[0]) {
                    return;
                }
                return jQuery.scrollWindowTo((this.$el.offset().top + $responseFieldEl.offset().top) - this.$responseFields.offset().top, 200, function () {
                    return _this.lockLeftWrapper();
                });
            };

            BuilderView.prototype.lockLeftWrapper = function () {
                return this.$fbLeft.data('locked', true);
            };

            BuilderView.prototype.unlockLeftWrapper = function () {
                return this.$fbLeft.data('locked', false);
            };

            BuilderView.prototype.handleFormUpdate = function () {
                if (this.updatingBatch) {
                    return;
                }
                this.formSaved = false;
                return this.saveFormButton.removeAttr('disabled').text(Formbuilder.options.dict.SAVE_FORM);
            };

            BuilderView.prototype.saveForm = function (e) {
                var payload;
                if (this.formSaved) {
                    return;
                }
                this.formSaved = true;
                this.saveFormButton.attr('disabled', true).text(Formbuilder.options.dict.ALL_CHANGES_SAVED);
                this.collection.sort();
                payload = JSON.stringify({
                    fields: this.collection.toJSON()
                });
                if (Formbuilder.options.HTTP_ENDPOINT) {
                    this.doAjaxSave(payload);
                }
                return this.formBuilder.trigger('save', payload);
            };

            BuilderView.prototype.doAjaxSave = function (payload) {
                var _this = this;
                return jQuery.ajax({
                    url: Formbuilder.options.HTTP_ENDPOINT,
                    type: Formbuilder.options.HTTP_METHOD,
                    data: payload,
                    contentType: "application/json",
                    success: function (data) {
                        var datum, _i, _len, _ref5;
                        _this.updatingBatch = true;
                        for (_i = 0, _len = data.length; _i < _len; _i++) {
                            datum = data[_i];
                            if ((_ref5 = _this.collection.get(datum.cid)) != null) {
                                _ref5.set({
                                    id: datum.id
                                });
                            }
                            _this.collection.trigger('sync');
                        }
                        return _this.updatingBatch = void 0;
                    }
                });
            };

            return BuilderView;

        })(Backbone.View);

        Formbuilder = (function () {
            Formbuilder.helpers = {
                defaultFieldAttrs: function (field_type) {
                    var attrs, _base;
                    attrs = {};
                    attrs[Formbuilder.options.mappings.LABEL] = 'Untitled';
                    attrs[Formbuilder.options.mappings.FIELD_TYPE] = field_type;
                    attrs[Formbuilder.options.mappings.REQUIRED] = true;
                    attrs['field_options'] = {};
                    return (typeof (_base = Formbuilder.fields[field_type]).defaultAttributes === "function" ? _base.defaultAttributes(attrs) : void 0) || attrs;
                },
                simple_format: function (x) {
                    return x != null ? x.replace(/\n/g, '<br />') : void 0;
                }
            };

            Formbuilder.options = {
                BUTTON_CLASS: 'fb-button',
                HTTP_ENDPOINT: '',
                HTTP_METHOD: 'POST',
                AUTOSAVE: true,
                CLEAR_FIELD_CONFIRM: false,
                mappings: {
                    FIELDCOL: 'fieldcol',
                    WRAPPERCOL: 'wrappercol',
                    FIELD_HEIGHT: 'fieldheight',
                    SIZE: 'field_options.size',
                    UNITS: 'field_options.units',
                    SHOW_BREAKLINE: 'show_breakline',
                    LABEL: 'label',
                    FIELD_TYPE: 'field_type',
                    CATE_ID: 'field_options.category_id',
                    SHOW_POS: 'show_position',
                    MAX_LEVEL: 'max_level',
                    SHOW_HEADING: 'show_heading',
                    SHOW_CITY: 'show_city',
                    SHOW_STATE: 'show_state',
                    SHOW_ZIPCODE: 'show_zipcode',
                    SHOW_ADDRESS: 'show_address',
                    SHOW_COUNTRY: 'show_country',
                    LIMIT: 'limit',
                    DEFAULT: 'default',
                    WIDTH: 'width',
                    HEIGHT: 'height',
                    RADIUS: 'radius',
                    DEFAULT_ADDRESS: 'address',
                    DEFAULT_LAT: 'default_lat',
                    DEFAULT_LONG: 'default_long',
                    REQUIRED: 'required',
                    ADMIN_ONLY: 'admin_only',
                    OPTIONS: 'field_options.options',
                    DESCRIPTION: 'field_options.description',
                    INCLUDE_OTHER: 'field_options.include_other_option',
                    INCLUDE_BLANK: 'field_options.include_blank_option',
                    INTEGER_ONLY: 'field_options.integer_only',
                    MIN: 'field_options.min',
                    MAX: 'field_options.max',
                    MINLENGTH: 'field_options.minlength',
                    MAXLENGTH: 'field_options.maxlength',
                    LENGTH_UNITS: 'field_options.min_max_length_units',
                    IMAGE_TYPE: 'image_type',
                    IMAGE_MAXIMUM_SIZE: 'image_maximum_size',
                },
                dict: {
                    ALL_CHANGES_SAVED: 'All changes saved',
                    SAVE_FORM: 'Save form',
                    UNSAVED_CHANGES: 'You have unsaved changes. If you leave this page, you will lose those changes!'
                }
            };

            Formbuilder.fields = {};

            Formbuilder.inputFields = {};

            Formbuilder.nonInputFields = {};

            Formbuilder.registerField = function (name, opts) {
                var x, _i, _len, _ref5;
                _ref5 = ['view', 'edit'];
                for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
                    x = _ref5[_i];
                    opts[x] = _.template(opts[x]);
                }
                opts.field_type = name;
                Formbuilder.fields[name] = opts;
                if (opts.type === 'non_input') {
                    return Formbuilder.nonInputFields[name] = opts;
                } else {
                    return Formbuilder.inputFields[name] = opts;
                }
            };

            function Formbuilder(opts) {
                var args;
                if (opts == null) {
                    opts = {};
                }
                _.extend(this, Backbone.Events);
                args = _.extend(opts, {
                    formBuilder: this
                });
                this.mainView = new BuilderView(args);
            }

            return Formbuilder;

        })();

        window.Formbuilder = Formbuilder;

        if (typeof module !== "undefined" && module !== null) {
            module.exports = Formbuilder;
        } else {
            window.Formbuilder = Formbuilder;
        }

    }).call(this);


    (function () {
        Formbuilder.registerField('address', {
            order: 50,
            view: "<div class='input-line'>\n  <span class='street'>\n    <input type='text' />\n    <label>Address</label>\n  </span>\n</div>\n\n<div class='input-line'>\n  <span class='city'>\n    <input type='text' />\n    <label>City</label>\n  </span>\n\n  <span class='state'>\n    <input type='text' />\n    <label>State / Province / Region</label>\n  </span>\n</div>\n\n<div class='input-line'>\n  <span class='zip'>\n    <input type='text' />\n    <label>Zipcode</label>\n  </span>\n\n  <span class='country'>\n    <select><option>United States</option></select>\n    <label>Country</label>\n  </span>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/address']({ includeOther: true }) %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-home\"></span></span> Address",
            defaultAttributes: function (attrs) {
                attrs.show_address = 1;
                attrs.show_city = 1;
                attrs.show_state = 1;
                attrs.show_zipcode = 1;
                attrs.show_country = 1;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('model_dropdown', {
            order: 105,
            view: "<div class='inline-group'>\n<table class='workflow_table'>\n<tbody>\n<tr><th>Pos</th><th>Model 1</th><th>Model 2</th></tr>\n<tr><td style='width:25px'><b>1.</b></td><td>\n<select style='margin-right: 15px;'>\n<option value=''>Select Model 1</option>\n</select>\n</td><td>\n<select>\n<option value=''>\nSelect Model 2\n</option>\n</select>\n</td>\n</tbody>\n</table>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/model_dropdown']({ includeBlank: true }) %>",
            addButton: "<span class='symbol'><span class='fa fa-caret-down'></span></span> Model Dropdowns",
            defaultAttributes: function (attrs) {
                attrs.max_level = 2;
                attrs.show_position = 1;

                attrs.field_options.category_id = [
                    {
                        value: "",
                        label: "",
                        checked: false
                    }, {
                        value: "",
                        label: "",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });
    }).call(this);


    (function () {
        Formbuilder.registerField('checkboxes', {
            order: 10,
            view: "<% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n <% if(typeof (rf.get(Formbuilder.options.mappings.OPTIONS)[i].label) != 'undefined') { %>\n<div>\n    <label class='fb-option'>\n      <input type='checkbox' <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'checked' %> onclick=\"javascript: return false;\" />\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </label>\n  </div>\n<% } %>\n<% } %>\n\n<% if (rf.get(Formbuilder.options.mappings.INCLUDE_OTHER)) { %>\n  <div class='other-option'>\n    <label class='fb-option'>\n      <input type='checkbox' />\n      Other\n    </label>\n\n    <input type='text' />\n  </div>\n<% } %>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/options']({ includeOther: true }) %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-square-o\"></span></span> Checkboxes",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "",
                        checked: false
                    }, {
                        label: "",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);


    (function () {
        Formbuilder.registerField('subscription', {
            order: 60,
            view: "<% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n <% if(typeof (rf.get(Formbuilder.options.mappings.OPTIONS)[i].label) != 'undefined') { %>\n<div>\n    <label class='fb-option'>\n      <input type='checkbox' <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'checked' %> onclick=\"javascript: return false;\" />\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </label>\n  </div>\n<% } %>\n<% } %>\n\n<% if (rf.get(Formbuilder.options.mappings.INCLUDE_OTHER)) { %>\n  <div class='other-option'>\n    <label class='fb-option'>\n      <input type='checkbox' />\n      Other\n    </label>\n\n    <input type='text' />\n  </div>\n<% } %>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/subscription']({ includeOther: true }) %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-square-o\"></span></span> Subscription",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "Sign Up for Newsletter",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('rating', {
            order: 55,
            view: "<div class='inline-group'>\n<div class='rating small' data-role='rating' data-size='small' data-value='<%= rf.get(Formbuilder.options.mappings.DEFAULT) %>'>\n<% for (i=1; i<= (rf.get(Formbuilder.options.mappings.LIMIT) || 5); i++) { %>\n<span class='star <% if(i <= (rf.get(Formbuilder.options.mappings.DEFAULT) || 0)) { %>on<% } %>'></span><% } %>\n<span class='score'>Rating: <%= rf.get(Formbuilder.options.mappings.DEFAULT) %> stars</span>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/rating']({ includeBlank: true }) %>",
            addButton: "<span class='symbol'><span class='fa fa-star'></span></span> Rating",
            defaultAttributes: function (attrs) {
                attrs.limit = 5;
                attrs.default = 0;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });
    }).call(this);

    (function () {
        Formbuilder.registerField('google_map', {
            order: 70,
            view: "<div class='inline-group'>\n<div class='img-google-map'>&nbsp;</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/google_map']({ includeBlank: true }) %>",
            addButton: "<span class='symbol'><span class='fa fa-map-marker'></span></span> Google Map",
            defaultAttributes: function (attrs) {
                attrs.width = 550;
                attrs.height = 400;
                attrs.radius = 300;
                attrs.address = "";
                attrs.default_lat = 21.0199438;
                attrs.default_long = 105.81731119999995;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });
    }).call(this);

    (function () {
        Formbuilder.registerField('date', {
            order: 20,
            view: "<div class='input-line'>\n<input type='text' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' /></div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-calendar\"></span></span> Date",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('dropdown', {
            order: 24,
            view: "<select class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>'>\n  <% if (rf.get(Formbuilder.options.mappings.INCLUDE_BLANK)) { %>\n    <option value=''></option>\n  <% } %>\n\n  <% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n    <option <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'selected' %>>\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </option>\n  <% } %>\n</select>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/options']({ includeBlank: true }) %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-caret-down\"></span></span> Dropdown",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "",
                        checked: false
                    }, {
                        label: "",
                        checked: false
                    }
                ];
                attrs.field_options.include_blank_option = false;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('email', {
            order: 40,
            view: "<input type='text' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' />",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-envelope-o\"></span></span> Email",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {


    }).call(this);

    (function () {
        Formbuilder.registerField('number', {
            order: 30,
            view: "<input type='text' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' />\n<% if (units = rf.get(Formbuilder.options.mappings.UNITS)) { %>\n  <%= units %>\n<% } %>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/min_max']() %>\n<%= Formbuilder.templates['edit/units']() %>\n<%= Formbuilder.templates['edit/integer_only']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-number\">123</span></span> Number",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('paragraph', {
            order: 5,
            view: "<textarea class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>'></textarea>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcoltextarea']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/min_max_length']() %>",
            addButton: "<span class=\"symbol\">&#182;</span> Paragraph",
            defaultAttributes: function (attrs) {
                attrs.field_options.size = 'small';
                attrs.fieldcol = 12;
                attrs.fieldheight = '200px';
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('price', {
            order: 45,
            view: "<div class='input-line'>\n  <span class='above-line' style='width: 5%'>$</span>\n  <span class='dolars'>\n    <input type='text' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>'/></div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-usd\"></span></span> Price",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('radio', {
            order: 15,
            view: "<% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n<% if(typeof(rf.get(Formbuilder.options.mappings.OPTIONS)[i].label) != 'undefined') { %>\n <div>\n    <label class='fb-option'>\n      <input type='radio' <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'checked' %> onclick=\"javascript: return false;\" />\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </label>\n  </div>\n<% } %>\n<% } %>\n\n<% if (rf.get(Formbuilder.options.mappings.INCLUDE_OTHER)) { %>\n  <div class='other-option'>\n    <label class='fb-option'>\n      <input type='radio' />\n      Other\n    </label>\n\n    <input type='text' />\n  </div>\n<% } %>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/options']({ includeOther: true }) %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-circle-o\"></span></span> Multiple Choice",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "",
                        checked: false
                    }, {
                        label: "",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('section_break', {
            order: 0,
            type: 'non_input',
            view: "",
            edit: "<div class='fb-edit-section-header'>Label</div>\n<input style='width: 100%' type='text' data-rv-input='model.<%= Formbuilder.options.mappings.LABEL %>' /><%= Formbuilder.templates['edit/showbreakline']() %>\n",
            addButton: "<span class='symbol'><span class='fa fa-minus'></span></span> Section Break",
            defaultAttributes: function (attrs) {
                attrs.show_breakline = 1;
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('file_upload', {
            order: 40,
            type: 'non_input',
            view: "<input type='file'/>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/file_upload']() %>",
            addButton: "<span class='symbol'><span class='fa fa-upload'></span></span> File Upload",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.image_type = 'jpg,png,jpeg,gif';
                attrs.image_maximum_size = 1;
                return attrs;
            }
        });
    }).call(this);

    (function () {
        Formbuilder.registerField('text', {
            order: 0,
            view: "<input type='text' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' />",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/min_max_length']() %>",
            addButton: "<span class='symbol'><span class='fa fa-font'></span></span> Text",
            defaultAttributes: function (attrs) {
                attrs.field_options.size = 'small';
                attrs.fieldcol = 12;
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('time', {
            order: 25,
            view: "<div class='input-line'>\n  <span class='hours'>\n    <input type=\"text\" />\n    <label>HH</label>\n  </span>\n\n  <span class='above-line'>:</span>\n\n  <span class='minutes'>\n    <input type=\"text\" />\n    <label>MM</label>\n  </span>\n\n  <span class='above-line'>:</span>\n\n  <span class='seconds'>\n    <input type=\"text\" />\n    <label>SS</label>\n  </span>\n\n  <span class='am_pm'>\n    <select>\n      <option>AM</option>\n      <option>PM</option>\n    </select>\n  </span>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-clock-o\"></span></span> Time",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('website', {
            order: 35,
            view: "<input type='text' placeholder='http://' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' />",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-link\"></span></span> Website",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    this["Formbuilder"] = this["Formbuilder"] || {};
    this["Formbuilder"]["templates"] = this["Formbuilder"]["templates"] || {};

    this["Formbuilder"]["templates"]["edit/base"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p +=
                ((__t = ( Formbuilder.templates['edit/base_header']() )) == null ? '' : __t) +
                '\n' +
                ((__t = ( Formbuilder.templates['edit/common']() )) == null ? '' : __t) +
                '\n' +
                ((__t = ( Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].edit({rf: rf}) )) == null ? '' : __t) +
                '\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/base_header"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-field-label\'>\n  <span data-rv-text="model.' +
                ((__t = ( Formbuilder.options.mappings.LABEL )) == null ? '' : __t) +
                '"></span>\n  <code class=\'field-type\' data-rv-text=\'model.' +
                ((__t = ( Formbuilder.options.mappings.FIELD_TYPE )) == null ? '' : __t) +
                '\'></code>\n  <span class=\'fa fa-arrow-right pull-right\'></span>\n'
            //__p += 'Id: <span id="currentid"> ' + currentModel.cid + ' </span>';
            __p += '</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/base_non_input"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p +=
                ((__t = ( Formbuilder.templates['edit/base_header']() )) == null ? '' : __t) +
                '\n' +
                ((__t = ( Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].edit({rf: rf}) )) == null ? '' : __t) +
                '\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/checkboxes"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = ( Formbuilder.options.mappings.REQUIRED )) == null ? '' : __t) +
                '\' />\n  Required\n</label>\n<!-- label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = ( Formbuilder.options.mappings.ADMIN_ONLY )) == null ? '' : __t) +
                '\' />\n  Admin only\n</label -->';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/fieldheight"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="afield" style="margin-top: 20px;"><span>Field Height</span><select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.FIELD_HEIGHT )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/showbreakline"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="afield" style="margin-top: 20px;"><span>Show Breakline</span><select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SHOW_BREAKLINE )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';
        }
        return __p
    };


    this["Formbuilder"]["templates"]["edit/bootstrapcoltextarea"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'><div class="row"><div class="col-sm-12">Bootstrap Column</div></div>';
            __p += '<div class="row">';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Wrapper Width</div>\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.WRAPPERCOL )) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Field Width</div>\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.FIELDCOL )) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Field Height</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.FIELD_HEIGHT )) == null ? '' : __t) +
                '" style="width: 100px" />';
            __p += '</div>';
            __p += '</div>';

            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/bootstrapcol"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'><div class="row"><div class="col-sm-12">Bootstrap Column</div></div>';
            __p += '<div class="row">';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Wrapper Width</div>\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.WRAPPERCOL )) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Field Width</div>\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.FIELDCOL )) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/bootstrapwrappercol"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'><div class="row"><div class="col-sm-12">Bootstrap Column</div></div>';
            __p += '<div class="row">';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Wrapper Width</div>\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.WRAPPERCOL )) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };


    this["Formbuilder"]["templates"]["edit/common"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Label</div>\n\n<div class=\'fb-common-wrapper\'>\n  <div class=\'fb-label-description\'>\n    ' +
                ((__t = ( Formbuilder.templates['edit/label_description']() )) == null ? '' : __t) +
                '\n  </div>\n  <div class=\'fb-common-checkboxes\'>\n    ' +
                ((__t = ( Formbuilder.templates['edit/checkboxes']() )) == null ? '' : __t) +
                '\n  </div>\n  <div class=\'fb-clear\'></div>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/integer_only"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Integer only</div>\n<label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = ( Formbuilder.options.mappings.INTEGER_ONLY )) == null ? '' : __t) +
                '\' />\n  Only accept integers\n</label>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/label_description"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<input type=\'text\' data-rv-input=\'model.' +
                ((__t = ( Formbuilder.options.mappings.LABEL )) == null ? '' : __t) +
                '\' />\n<textarea data-rv-input=\'model.' +
                ((__t = ( Formbuilder.options.mappings.DESCRIPTION )) == null ? '' : __t) +
                '\'\n  placeholder=\'Add a longer description to this field\'></textarea>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/min_max"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Minimum / Maximum</div>\n\nAbove\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.MIN )) == null ? '' : __t) +
                '" style="width: 50px" />\n\n&nbsp;&nbsp;\n\nBelow\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.MAX )) == null ? '' : __t) +
                '" style="width: 50px" />\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/min_max_length"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Length Limit</div>\n\nMin\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.MINLENGTH )) == null ? '' : __t) +
                '" style="width: 50px" />\n\n&nbsp;&nbsp;\n\nMax\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.MAXLENGTH )) == null ? '' : __t) +
                '" style="width: 50px" />\n\n&nbsp;&nbsp;\n\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.LENGTH_UNITS )) == null ? '' : __t) +
                '" style="width: 110px;min-width: auto;">\n  <option value="characters">characters</option>\n  <option value="words">words</option>\n</select>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/options"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

        function print() {
            __p += __j.call(arguments, '')
        }

        with (obj) {
            __p += '<br/><div class=\'fb-edit-section-helper\'>Add images to select fields from media folder with the code for option name: \n<br/> \nOption <i>{{media url="myfolder/myfile.png"}}</i> <br/> myfolder/ is folder path under <strong>media/</strong> folder<br/><br/>For example:<br/> sony{{media url="brand/sony.png"}}</div>\n';

            __p += '<div class=\'fb-edit-section-header\'>Options</div>\n\n';
            if (typeof includeBlank !== 'undefined') {
                ;
                __p += '\n  <label>\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = ( Formbuilder.options.mappings.INCLUDE_BLANK )) == null ? '' : __t) +
                    '\' />\n    Include blank\n  </label>\n';
            }
            ;
            __p += '\n\n<div class=\'option\' data-rv-each-option=\'model.' +
                ((__t = ( Formbuilder.options.mappings.OPTIONS )) == null ? '' : __t) +
                '\'>\n  <input type="checkbox" class=\'js-default-updated\' data-rv-checked="option:checked" />\n  <input type="text" data-rv-input="option:label" class=\'option-label-input\' />\n  <a class="js-add-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Add Option"><i class=\'fa fa-plus-circle\'></i></a>\n  <a class="js-remove-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Remove Option"><i class=\'fa fa-minus-circle\'></i></a>\n</div>\n\n';
            if (typeof includeOther !== 'undefined') {
                ;
                __p += '\n  <label>\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = ( Formbuilder.options.mappings.INCLUDE_OTHER )) == null ? '' : __t) +
                    '\' />\n    Include "other"\n  </label>\n';
            }
            ;
            __p += '\n\n<div class=\'fb-bottom-add\'>\n  <a class="js-add-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '">Add option</a>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/subscription"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

        function print() {
            __p += __j.call(arguments, '')
        }

        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Subscription Text</div>\n\n';
            if (typeof includeBlank !== 'undefined') {
                ;
                __p += '\n  <label>\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = ( Formbuilder.options.mappings.INCLUDE_BLANK )) == null ? '' : __t) +
                    '\' />\n    Include blank\n  </label>\n';
            }
            ;
            __p += '\n\n<div class=\'option\' data-rv-each-option=\'model.' +
                ((__t = ( Formbuilder.options.mappings.OPTIONS )) == null ? '' : __t) +
                '\'>\n  <input type="checkbox" class=\'js-default-updated\' data-rv-checked="option:checked" />\n  <input type="text" data-rv-input="option:label" class=\'option-label-input\' />\n  <a style="display:none;" class="js-add-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Add Option"><i class=\'fa fa-plus-circle\'></i></a>\n  <a style="display:none;" class="js-remove-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Remove Option"><i class=\'fa fa-minus-circle\'></i></a>\n</div>\n\n';
            if (typeof includeOther !== 'undefined') {
                ;
                __p += '\n  <label style="display:none;">\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = ( Formbuilder.options.mappings.INCLUDE_OTHER )) == null ? '' : __t) +
                    '\' />\n    Include "other"\n  </label>\n';
            }
            ;
            __p += '\n\n<div  style="display:none;" class=\'fb-bottom-add\'>\n  <a class="js-add-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '">Add option</a>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/size"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Size</div>\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SIZE )) == null ? '' : __t) +
                '">\n  <option value="small">Small</option>\n  <option value="medium">Medium</option>\n  <option value="large">Large</option>\n</select>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/rating"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Number Of Stars</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.LIMIT )) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Default Score</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.DEFAULT )) == null ? '' : __t) +
                '" style="width: 60px" />\n';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/model_dropdown"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

        function print() {
            __p += __j.call(arguments, '')
        }

        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Show Position</div>\n<select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SHOW_POS )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';

            __p += '<div class=\'fb-edit-section-header\'>Max Model Level</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.MAX_LEVEL )) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Data Model Categories</div>\n\n';
            __p += '\n\n<div class=\'option\' data-rv-each-option=\'model.' +
                ((__t = ( Formbuilder.options.mappings.CATE_ID )) == null ? '' : __t) +
                '\'>\n  <input type="checkbox" class=\'js-default-updated\' data-rv-checked="option:checked" />\n  <select class=\'option-label-input\' data-rv-value="option:value">';
            if (typeof(data_model_categories) && data_model_categories.length) {
                __p += '\n<option value="0">-- Select Model Category -- </option>';
                for (i = 0; i < data_model_categories.length; i++) {
                    __p += '\n<option value="' + data_model_categories[i]['value'] + '">' + data_model_categories[i]['label'] + '</option>';
                }
            }
            __p += '\n</select>\n  <a class="js-add-cate-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Add Option"><i class=\'fa fa-plus-circle\'></i></a>\n  <a class="js-remove-cate-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Remove Option"><i class=\'fa fa-minus-circle\'></i></a>\n</div>\n\n';

            __p += '\n\n<div class=\'fb-bottom-add\'>\n  <a class="js-add-cate-option ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '">Add option</a>\n</div>\n';

        }

        return __p
    };

    this["Formbuilder"]["templates"]["edit/address"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        __p += '<div class="address-settings">';
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>View Settings</div>';
            __p += '<div class="afield"><span>Show Address:</span> <select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SHOW_ADDRESS )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Show City:</span> <select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SHOW_CITY )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Show State:</span><select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SHOW_STATE )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Show Zipcode:</span><select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SHOW_ZIPCODE )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Show Country:</span><select data-rv-value="model.' +
                ((__t = ( Formbuilder.options.mappings.SHOW_COUNTRY )) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';
        }
        __p += '</div>';
        return __p
    };

    this["Formbuilder"]["templates"]["edit/google_map"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Map Width - Height</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.WIDTH )) == null ? '' : __t) +
                '" style="width: 60px" /> - <input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.HEIGHT )) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Radius</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.RADIUS )) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Default Latitude</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.DEFAULT_LAT )) == null ? '' : __t) +
                '" style="width: 140px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Default Longtitude</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.DEFAULT_LONG )) == null ? '' : __t) +
                '" style="width: 140px" />\n';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/file_upload"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;

        with (obj) {
            __p += '<div class="fb-edit-section-header">Label</div>';
            __p += '<div class="fb-common-wrapper">';
            __p += '<div class="fb-label-description">';
            __p += '<input type=\'text\' data-rv-input=\'model.' +
                ((__t = ( Formbuilder.options.mappings.LABEL )) == null ? '' : __t) +
                '\' />\n<textarea data-rv-input=\'model.' +
                ((__t = ( Formbuilder.options.mappings.DESCRIPTION )) == null ? '' : __t) +
                '\'\n  placeholder=\'Add a longer description to this field\'></textarea>';
            __p += '</div>';
            __p += '<label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = ( Formbuilder.options.mappings.REQUIRED )) == null ? '' : __t) +
                '\' />\n  Required\n</label>\n<!-- label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = ( Formbuilder.options.mappings.ADMIN_ONLY )) == null ? '' : __t) +
                '\' />\n  Admin only\n</label -->';
            __p += '</div>';
            __p += '<div class=\'fb-edit-section-header\'>Options</div>\n\n';
            __p += '<div class="fb-common-wrapper">';
            __p += '<div class="fb-label-description">';
            __p += '<div class="field-row">';
            __p += '<p>Field type</p>';
            __p += '<input name="image_type" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.IMAGE_TYPE )) == null ? '' : __t) +
                '" placeholder="png,jpg,gif,jpeg,zip,doc,pdf"/>';
            __p += '<div class="text-small">Comma-separated.</div>';
            __p += '</div>';
            __p += '<div class="field-row">';
            __p += '<p>Maximum Size(MB)</p>';
            __p += '<input name="image_maximum_size" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.IMAGE_MAXIMUM_SIZE )) == null ? '' : __t) +
                '"/>';
            __p += '</div>';
            __p += '</div>';

        }

        return __p
    };

    this["Formbuilder"]["templates"]["edit/units"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Units</div>\n<input type="text" data-rv-input="model.' +
                ((__t = ( Formbuilder.options.mappings.UNITS )) == null ? '' : __t) +
                '" />\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["page"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p +=
                //((__t = ( Formbuilder.templates['partials/save_button']() )) == null ? '' : __t) +
                '\n' +
                ((__t = ( Formbuilder.templates['partials/left_side']() )) == null ? '' : __t) +
                '\n' +
                ((__t = ( Formbuilder.templates['partials/right_side']() )) == null ? '' : __t) +
                '\n<div class=\'fb-clear\'></div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/add_field"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

        function print() {
            __p += __j.call(arguments, '')
        }

        with (obj) {
            __p += '<div class=\'fb-tab-pane active\' id=\'addField\'>\n  <div class=\'fb-add-field-types\'>\n    <div class=\'section\'>\n      ';
            _.each(_.sortBy(Formbuilder.inputFields, 'order'), function (f) {
                ;
                __p += '\n        <a data-field-type="' +
                    ((__t = ( f.field_type )) == null ? '' : __t) +
                    '" class="' +
                    ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                    '">\n          ' +
                    ((__t = ( f.addButton )) == null ? '' : __t) +
                    '\n        </a>\n      ';
            });
            ;
            __p += '\n    </div>\n\n    <div class=\'section\'>\n      ';
            _.each(_.sortBy(Formbuilder.nonInputFields, 'order'), function (f) {
                ;
                __p += '\n        <a data-field-type="' +
                    ((__t = ( f.field_type )) == null ? '' : __t) +
                    '" class="' +
                    ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                    '">\n          ' +
                    ((__t = ( f.addButton )) == null ? '' : __t) +
                    '\n        </a>\n      ';
            });
            ;
            __p += '\n    </div>\n  </div>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/edit_field"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-tab-pane\' id=\'editField\'>\n  <div class=\'fb-edit-field-wrapper\'></div>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/left_side"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-left\'>\n<div class="wrapper-left"><ul class=\'fb-tabs\'>\n    <li class=\'active\'><a data-target=\'#addField\'>Add new field</a></li>\n    <li><a data-target=\'#editField\'>Edit field</a></li>\n  </ul>\n\n  <div class=\'fb-tab-content\'>\n    ' +
                ((__t = ( Formbuilder.templates['partials/add_field']() )) == null ? '' : __t) +
                '\n    ' +
                ((__t = ( Formbuilder.templates['partials/edit_field']() )) == null ? '' : __t) +
                '\n  </div></div>\n</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/right_side"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-right\'>\n  <div class=\'fb-no-response-fields\'>No response fields</div>\n  <div class=\'fb-response-fields\'></div>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/save_button"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-save-wrapper\'>\n  <button class=\'js-save-form ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '\'></button>\n</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/base"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'subtemplate-wrapper\'>\n  <div class=\'cover\'></div>\n  ' +
                ((__t = ( Formbuilder.templates['view/label']({rf: rf}) )) == null ? '' : __t) +
                '\n\n  ' +
                ((__t = ( Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].view({rf: rf}) )) == null ? '' : __t) +
                '\n\n  ' +
                ((__t = ( Formbuilder.templates['view/description']({rf: rf}) )) == null ? '' : __t) +
                '\n  ' +
                ((__t = ( Formbuilder.templates['view/duplicate_remove']({rf: rf}) )) == null ? '' : __t) +
                '\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/base_non_input"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        var field_type = obj.rf.attributes.field_type;

        with (obj) {
            __p += '<div class=\'subtemplate-wrapper\'>\n  <div class=\'cover\'></div>\n  ';

            if (field_type == 'section_break') {
                ((__p += ( Formbuilder.templates['view/section_break_label']({rf: rf}) )) == null ? '' : __t);
            }

            __p += '\n\n  ';

            if (field_type == 'file_upload') {
                ((__p += ( Formbuilder.templates['view/label']({rf: rf}) )) == null ? '' : __t);
            }

            ((__p += ( Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].view({rf: rf}) )) == null ? '' : __t);

            ((__p += ( Formbuilder.templates['view/description']({rf: rf}) )) == null ? '' : __t);

            __p += '\n\n  ' + '\n\n  ' +
                ((__t = ( Formbuilder.templates['view/duplicate_remove']({rf: rf}) )) == null ? '' : __t) +
                '\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/description"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<span class=\'help-block\'>\n  ' +
                ((__t = ( Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.DESCRIPTION)) )) == null ? '' : __t) +
                '\n</span>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/duplicate_remove"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'actions-wrapper\'>\n  <a class="js-duplicate ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Duplicate Field"><i class=\'fa fa-plus-circle\'></i></a>\n  <a class="js-clear ' +
                ((__t = ( Formbuilder.options.BUTTON_CLASS )) == null ? '' : __t) +
                '" title="Remove Field"><i class=\'fa fa-minus-circle\'></i></a>\n</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/label"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

        function print() {
            __p += __j.call(arguments, '')
        }

        with (obj) {
            __p += '<label>\n  <span>' +
                ((__t = ( Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.LABEL)) )) == null ? '' : __t) +
                '\n  ';
            if (rf.get(Formbuilder.options.mappings.REQUIRED)) {
                ;
                __p += '\n    <abbr title=\'required\'>*</abbr>\n  ';
            }
            ;
            __p += '\n</label>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/section_break_label"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;

        function print() {
            __p += __j.call(arguments, '')
        }

        with (obj) {
            __p += '<div class="field-section-break">\n  <span>' +
                ((__t = ( Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.LABEL)) )) == null ? '' : __t) +
                '</span> ';
            __p += '\n</div>\n';

        }
        return __p
    };

});