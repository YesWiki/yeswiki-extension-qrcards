// The `qrcard` field in the form builder: the entry shows its QR card where the field is placed.
window.formBuilderFields = window.formBuilderFields || {}
window.formBuilderFields.qrcard = ({ readConf }) => ({
  field: {
    label: _t('QRCARDS_FORM_BUILDER_LABEL'),
    name: 'qrcard',
    attrs: { type: 'qrcard' },
    icon: '<svg class="yw-icon" aria-hidden="true"><use href="src/assets/icons.svg#qrcode"/></svg>',
  },
  editorHint: _t('QRCARDS_FORM_BUILDER_HINT'),
  attributes: {
    read_access: readConf,
  },
  disabledAttributes: ['label', 'name', 'default', 'required', 'hint'],
  advancedAttributes: ['read_access'],
})
