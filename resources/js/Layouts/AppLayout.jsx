import React, { useMemo, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { Avatar, Breadcrumb, Button, Dropdown, Layout, Menu, Space, Typography } from 'antd';
import {
  BarChartOutlined, DownOutlined, FileSearchOutlined, LogoutOutlined,
  MenuFoldOutlined, MenuUnfoldOutlined, SafetyCertificateOutlined,
  TeamOutlined, UserOutlined,
} from '@ant-design/icons';

const { Header, Sider, Content, Footer } = Layout;

export default function AppLayout({ children, title = 'Audit Finding Monitoring' }) {
  const { auth, url } = usePage().props;
  const currentUrl = url || window.location.pathname;
  const user = auth?.user;
  const [collapsed, setCollapsed] = useState(false);
  const isAdmin = user?.role === 'ADMIN';

  const menuItems = useMemo(() => [
    { key: '/dashboard', icon: <BarChartOutlined />, label: <Link href="/dashboard">Dashboard</Link> },
    { key: '/findings', icon: <FileSearchOutlined />, label: <Link href="/findings">Temuan Audit</Link> },
    { key: '/reports', icon: <BarChartOutlined />, label: <Link href="/reports">Report</Link> },
    ...(isAdmin ? [{ type: 'divider' }, { key: '/audits', icon: <SafetyCertificateOutlined />, label: <Link href="/audits">Daftar Audit</Link> }, { key: '/users', icon: <TeamOutlined />, label: <Link href="/users">User Management</Link> }] : []),
  ], [isAdmin]);
  const selected = menuItems.filter(item => item.key && currentUrl.startsWith(item.key)).sort((a,b) => b.key.length-a.key.length)[0]?.key || '/dashboard';
  const userMenu = { items: [
    { key: 'profile', label: <span>{user?.email || 'Akun pengguna'}</span>, disabled: true, icon: <UserOutlined /> },
    { type: 'divider' },
    { key: 'logout', icon: <LogoutOutlined />, label: <Link href="/logout" method="post" as="button">Keluar</Link> },
  ] };

  return <Layout className="admin-layout">
    <Sider className="admin-sider" width={252} collapsedWidth={78} collapsible collapsed={collapsed} trigger={null}>
      <Link href="/dashboard" className="brand-lockup">
        <span className="brand-mark"><SafetyCertificateOutlined /></span>
        {!collapsed && <span className="brand-name">Audit<span>Monitor</span></span>}
      </Link>
      {!collapsed && <div className="menu-caption">MENU UTAMA</div>}
      <Menu theme="dark" mode="inline" selectedKeys={[selected]} items={menuItems} className="admin-menu" />
      {!collapsed && <div className="sider-note"><span className="sider-note-icon"><SafetyCertificateOutlined /></span><b>Audit Finding Monitoring</b><small>Monitoring temuan dalam satu tempat.</small></div>}
    </Sider>
    <Layout className={`admin-main${collapsed ? ' collapsed' : ''}`}>
      <Header className="admin-header">
        <Space size={16}>
          <Button type="text" className="collapse-button" icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />} onClick={() => setCollapsed(!collapsed)} />
          <Breadcrumb items={[{ title: 'Monitoring' }, { title }]} />
        </Space>
        <Dropdown menu={userMenu} placement="bottomRight" trigger={['click']}>
          <button className="account-button"><Avatar icon={<UserOutlined />} size={36} /><span className="account-copy"><b>{user?.name || 'Pengguna'}</b><small>{isAdmin ? 'Administrator' : 'Auditor'}</small></span><DownOutlined className="account-chevron" /></button>
        </Dropdown>
      </Header>
      <Content className="admin-content">
        <div className="page-heading"><div><Typography.Title level={3}>{title}</Typography.Title><Typography.Text type="secondary">Kelola dan pantau tindak lanjut hasil audit.</Typography.Text></div></div>
        {children}
      </Content>
      <Footer className="admin-footer">Audit Finding Monitoring <span>•</span> Sistem monitoring temuan audit</Footer>
    </Layout>
  </Layout>;
}
