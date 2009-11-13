// vim:ts=3:sw=3
Ext.ns('Admin', 'Admin.Panel');

Admin.Panel.Dashboard = Ext.extend(Ext.Panel, {
	title: 'Dashboard',
	frame: true,
	initComponent: function() {
		Admin.Panel.Dashboard.superclass.initComponent.apply(this, arguments);
	}
});
Ext.reg('dashboardpanel', Admin.Panel.Dashboard);
