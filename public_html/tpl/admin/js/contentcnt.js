// vim:ts=3:sw=3
Ext.ns('Admin', 'Admin.Container');

Admin.Container.Content = Ext.extend(Ext.Container, {
	layout: 'border',
	defaults: {
		frame: true
	},
	initComponent: function() {

		var config = {
			items: [{
				region: 'west',
				width: 200,
				collapsible: true,
				title: 'Content Tree'
			}, {
				region: 'center',
				title: 'Content'
			}]
		};

		Ext.apply(this, Ext.apply(this.initialConfig, config));
		Admin.Container.Content.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('contentcnt', Admin.Container.Content);
