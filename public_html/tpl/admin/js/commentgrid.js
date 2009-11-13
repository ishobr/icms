// vim:ts=3:sw=3
Ext.ns('Admin', 'Admin.grid');

Admin.grid.Comment = Ext.extend(Ext.grid.GridPanel, {
	title: 'Comment Management',
	stripeRows: true,
	frame: true,
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
				{name: 'name', allowBlank: false},
				{name: 'email', allowBlank: false}
			]
		});

		var writer = new Ext.data.JsonWriter({
			writeAllFields: true
		});

		this.store = new Ext.data.Store({
			proxy: proxy,
			reader: reader,
			writer: writer
		});

		var config = {
			cm: new Ext.grid.ColumnModel({
            defaults: {
               sortable: true,
               width: 100
            },
				columns: [
					new Ext.grid.RowNumberer({header: 'No'}),
					{header: 'Komentar', dataIndex: 'uid'},
					{header: 'Content', dataIndex: 'name'},
					{header: 'Pengirim', dataIndex: 'email'}
				]
			}),
			tbar: [{
				text: 'Setujui Komentar',
				iconCls: 'icon-cmt-ok',
				disabled: true
			}, {
				text: 'Hapus Komentar',
				iconCls: 'icon-cmt-del',
				disabled: true
			}]
		};
	
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Admin.grid.Comment.superclass.initComponent.apply(this, arguments);
	}
	,addUser: function() {
		if (!this.win) {
			this.win = new Ext.Window({
				width: 350
				,autoHeight: true
				,title: 'Add User'
				,modal: true
				,closeAction: 'hide'
				,items: [{
					xtype: 'userform'	
				}]
			});
		}

		this.win.items.itemAt(0).on('save', function() {
			this.store.reload();
			this.win.hide();
		}, this);

		this.win.show();
	}
});
Ext.reg('commentgrid', Admin.grid.Comment);
