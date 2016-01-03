module.exports = window.ProfilefieldMixin = {

    props: ['isAdmin', 'profilevalues', 'user', 'field', 'form'],

    methods: {
        getDataObject: function (defaultValue) {
            if (this.isAdmin) {
                this.field.data.value = this.field.data.value || defaultValue;
                return this.field.data;
            }
            return this.getProfilevalue(defaultValue);
        },
        getProfilevalue: function (defaultValue) {
            var index = _.findIndex(this.profilevalues, 'field_id', this.field.id),
                defaultProfilevalue = {
                    id: 0,
                    user_id: this.user.id,
                    field_id: this.field.id,
                    multiple: this.field.data.multiple || 0,
                    value: defaultValue,
                    prepared: this.field.prepared
                };
            if (index === -1) {
                index = this.profilevalues.length;
                this.profilevalues.push(defaultProfilevalue);
            }
            //multiple setting changed, convert value
            if (this.field.data.multiple && this.profilevalues[index].multiple != this.field.data.multiple) {

                this.profilevalues[index].multiple = this.field.data.multiple;

                if (typeof this.profilevalues[index].value === 'object' && !this.profilevalues[index].multiple) {
                    this.profilevalues[index].value = this.profilevalues[index].value[0];
                }
                if (typeof this.profilevalues[index].value !== 'object' && this.profilevalues[index].multiple) {
                    this.profilevalues[index].value = [this.profilevalues[index].value];
                }

            }
            return this.profilevalues[index];
        },
        fieldInvalid: function () {
            return this.form[this.fieldid].invalid;
        },
        classes: function (classes_array, classes_string) {
            return (classes_array || []).concat(String(classes_string || '').split(' '));
        }

    },

    computed: {
        fieldRequired: function () {
            return this.field.data.required && !this.isAdmin ? true : false;
        },
        fieldLabel: function () {
            return this.isAdmin ? 'Default value' : this.field.label;
        }
    }

};