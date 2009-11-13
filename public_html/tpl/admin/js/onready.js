// vim:ts=3:sw=3

Ext.onReady(function() {
   Ext.QuickTips.init();

	function setContent(cmp) {
		var pressed = menu.bar.find('pressed', true);
		for (var i = 0, len = pressed.length; i < len; i++ ) {
			pressed[i].toggle(false);
		}
		menu[cmp].toggle(true);
		this.removeAll(true);
		this.add({
			xtype: cmp,
		});
		view.doLayout();
	}

	var content = new Ext.Container({
			region: 'center',
			layout: 'fit'
	});

	var menu = new Ext.Panel({
		region: 'north',
		autoHeight: true,
		border: false,
		items: {
			xtype: 'container',
			height: 60
		},
		bbar: new Ext.Toolbar({
			ref: 'bar',
			layout: 'hbox',
			layoutConfig: {pack: 'center'},
			defaults: {
				iconAlign: 'top',
				width: 10,
				scale: 'medium'
			},
			items: [{
				text: 'Dashborad',
				ref: '../dashboardpanel',
				width: 60,
				iconCls: 'icon-dashboard',
				handler: setContent.createDelegate(content, ['dashboardpanel'])
			}, ' ', {
				text: 'Content',
				ref: '../contentcnt',
				width: 60,
				pressed: true,
				iconCls: 'icon-content',
				handler: setContent.createDelegate(content, ['contentcnt'])
			}, ' ', {
				text: 'Comment',
				ref: '../commentgrid',
				width: 60,
				iconCls: 'icon-comment',
				handler: setContent.createDelegate(content, ['commentgrid'])
			}, {
				text: 'Statistics',
				ref: '../statpanel',
				width: 60,
				iconCls: 'icon-stat',
				handler: setContent.createDelegate(content, ['statpanel'])
			}, {
				text: 'User',
				ref: '../usergrid',
				width: 60,
				iconCls: 'icon-user',
				handler: setContent.createDelegate(content, ['usergrid'])
			}]
		})
	});


	var view = new Ext.Viewport({
		layout: 'border',
		items: [menu, content]
	});


	content.add({
		xtype: 'contentcnt'
	});

	view.doLayout();

});
