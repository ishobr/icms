// vim:ts=3:sw=3
Ext.ns('Admin');

Admin.UserGrid = Ext.extend(Ext.grid.GridPanel, {
	title: 'User Management',
	loadMask: true,
	stripeRows: true,
	initComponent: function() {

		var proxy = new Ext.data.HttpProxy({
			api: {
				read: '/user/ajaxGet',
				create: '/user/ajaxSave',
				update: '/user/ajaxUpdate',
				destroy: '/user/ajaxDelete'
			}
		});

		var reader = new Ext.data.JsonReader({
			root: 'data',
			fields: [
				{name: 'id', type: 'int'},
				{name: 'uid', allowBlank: false},
				{name: 'pwd'},
				{name: 'name', allowBlank: false},
				{name: 'mail', allowBlank: false},
				{name: 'phone'},
				{name: 'newsletter', type: 'int'},
				{name: 'level', type: 'int', allowBlank: false}
			]
		});

		var writer = new Ext.data.JsonWriter({
			writeAllFields: true
		});

		this.store = new Ext.data.Store({
			proxy: proxy,
			reader: reader,
			writer: writer,
			remoteSort: true,
			autoLoad: true
		});

			this.newsCombo = new Ext.form.ComboBox({
					fieldLabel: 'Newsletter',
						store: {
							xtype: 'arraystore',
							fields: ['id', 'name'],
							data: [[0, 'Tidak'], [1, 'Individual'], [2, 'Digest']]
						},
						mode: 'local',
						triggerAction: 'all',
						hiddenName: 'newsletter',
						hiddenValue: 'id',
						valueField: 'id',
						displayField: 'name'
			});

		var config = {

			cm: new Ext.grid.ColumnModel({
            defaults: {
               sortable: true,
               width: 150
            },
				columns: [
					new Ext.grid.RowNumberer({header: 'No'}),
					{header: 'UserID', dataIndex: 'uid'},
					{header: 'Nama', id: 'namecol', dataIndex: 'name'},
					{header: 'Email', dataIndex: 'mail', width: 200},
					{header: 'Telepon', dataIndex: 'phone'},
					{header: 'Newsletter', dataIndex: 'newsletter', renderer: Ext.util.Format.comboRenderer(this.newsCombo)},
					{header: 'Level', dataIndex: 'level'}
				]
			}),
			autoExpandColumn: 'namecol',
			sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
			tbar: [{
				text: 'Tambah User',
				iconCls: 'icon-user-add',
				handler: this.addItem,
				scope: this
			}, {
				text: 'Edit User',
				ref: '../editBtn',
				iconCls: 'icon-user-edit',
				handler: this.editItem,
				scope: this,
				disabled: true
			/*}, {
				text: 'Hapus User',
				ref: '../delBtn',
				iconCls: 'icon-user-del',
				disabled: true*/
			}],
			bbar: new Ext.PagingToolbar({
				store: this.store
			})
		};
	
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Admin.UserGrid.superclass.initComponent.apply(this, arguments);

		this.getSelectionModel().on('selectionchange', this.onSelect, this);	

	},
	onSelect: function(sm) {
		var count = sm.getCount();
		//this.delBtn.setDisabled(!count);
		this.editBtn.setDisabled(!count);
	}
	,addItem: function() {

		if (!this.win) {

			this.form = new Ext.form.FormPanel({
				frame: true,
				bodyStyle: 'padding:8px',
				items: [{
					xtype: 'fieldset',
					title: 'Login Information',
					defaultType: 'textfield',
					defaults: {
						anchor: '95%'
					},
					items: [{
						xtype: 'hidden',
						name: 'id'
					}, {
						fieldLabel: 'UserID',
						allowBlank: false,
						vtype: 'alpha',
						name: 'uid'
					}, {
						fieldLabel: 'Password',
						inputType: 'password',
						name: 'pwd'
					}]
				}, {
					xtype: 'fieldset',
					title: 'User Information',
					defaultType: 'textfield',
					defaults: {
						anchor: '95%'
					},
					items: [{
						fieldLabel: 'Name',
						allowBlank: false,
						name: 'name'
					}, {
						fieldLabel: 'E-mail',
						allowBlank: false,
						vtype: 'email',
						name: 'mail'
					}, {
						fieldLabel: 'Phone',
						name: 'phone'
					}, {
						fieldLabel: 'Level',
						name: 'level',
						xtype: 'numberfield',
						minValue: 1,
						maxValue: 99,
						value: 99 
					}, this.newsCombo]
				}]
			});

			this.win = new Ext.Window({
				width: 400
				,autoHeight: true
				,title: 'Add User'
				,modal: true
				,closeAction: 'hide'
				,items: this.form
				,buttons: [{
					text: 'Save',
					handler: this.saveForm,
					scope:	this
				}]
			});
			this.win.on('show', function() {
				this.win.center();
			}, this);
		}

		this.form.getForm().reset();
		this.win.show();
	},
	editItem: function() {
		this.addItem();
		var rec = this.getSelectionModel().getSelected();
		this.form.getForm().loadRecord(rec);
	},
	saveForm: function() {
		if (!this.form.getForm().isValid()) return;
		var v = this.form.getForm().getValues();
		if (v.id) {
			var rec = this.getSelectionModel().getSelected();
			rec.beginEdit();
			rec.fields.each(function(i) {
				rec.set(i.name, v[i.name]);
			});
			rec.endEdit();
		} else {
			var rec = new this.store.recordType(v);
			this.store.add(rec);
		}
		this.win.hide();
	}
});
Ext.reg('usergrid', Admin.UserGrid);
