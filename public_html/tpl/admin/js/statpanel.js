// vim:ts=3:sw=3
Ext.ns('Admin', 'Admin.Panel');

Admin.Panel.Stat = Ext.extend(Ext.Panel, {
	title: 'Statistik',
	frame: true,
	initComponent: function() {
		Admin.Panel.Stat.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('statpanel', Admin.Panel.Stat);
