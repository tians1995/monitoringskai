import React from 'react';
import { Link } from '@inertiajs/react';
import { Alert, Card, Empty, Progress, Statistic, Tag, Typography } from 'antd';
import { CalendarOutlined, CheckCircleOutlined, ClockCircleOutlined, ExclamationCircleOutlined, FileSearchOutlined } from '@ant-design/icons';
import { CartesianGrid, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import AppLayout from '../Layouts/AppLayout';

const statusTag = {
    open: <Tag color="blue">Open</Tag>,
    overdue: <Tag color="red">Overdue</Tag>,
    closed: <Tag color="green">Closed</Tag>,
};

export default function Dashboard({ counts = {}, due = [], months = [], monthlyCounts = {}, monthlyProjects = [], insights = [] }) {
    const monthName = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(new Date());
    const activeProjects = monthlyProjects.filter(project => project.open > 0 || project.overdue > 0);
    const stats = [
        { label: 'Target bulan ini', value: monthlyCounts.total || 0, icon: <CalendarOutlined />, tone: 'blue' },
        { label: 'Masih open', value: monthlyCounts.open || 0, icon: <FileSearchOutlined />, tone: 'indigo' },
        { label: 'Jatuh tempo ≤ 7 hari', value: monthlyCounts.due_soon || 0, icon: <ClockCircleOutlined />, tone: 'amber' },
        { label: 'Overdue', value: monthlyCounts.overdue || 0, icon: <ExclamationCircleOutlined />, tone: 'red' },
        { label: 'Sudah closed', value: monthlyCounts.closed || 0, icon: <CheckCircleOutlined />, tone: 'green' },
    ];

    return <AppLayout title="Dashboard">
        <div className="dashboard-period"><span><CalendarOutlined /> Periode target</span><strong>{monthName}</strong></div>

        <div className="grid grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
            {stats.map(item => <Card className={`analytics-card analytics-${item.tone}`} key={item.label}>
                <div className="analytics-card-top"><span>{item.label}</span><i>{item.icon}</i></div>
                <Statistic value={item.value} />
                <small>{item.label === 'Target bulan ini' ? 'Berdasarkan tanggal target' : `Dari ${monthlyCounts.total || 0} target bulan ini`}</small>
            </Card>)}
        </div>

        <div className="grid xl:grid-cols-3 gap-5 mb-5">
            <Card className="dashboard-panel xl:col-span-2" title="Status temuan per audit / proyek" extra={<span className="panel-caption">Bulan berjalan</span>}>
                {activeProjects.length ? <div className="audit-summary-list">{activeProjects.map(project => <div className="audit-summary-row" key={project.id || project.project}>
                    <div className="audit-summary-name"><b>{project.project} {project.year || ''}</b><small>{project.total} temuan bertarget bulan ini</small></div>
                    <div className="audit-summary-counts"><span className="summary-closed"><i />Closed <b>{project.closed}</b></span><span className="summary-open"><i />Open <b>{project.open}</b></span><span className="summary-overdue"><i />Overdue <b>{project.overdue}</b></span></div>
                    <div className="audit-summary-progress"><Progress percent={project.total ? Math.round((project.closed / project.total) * 100) : 0} showInfo={false} strokeColor="#32a875" /><small>{project.total ? Math.round((project.closed / project.total) * 100) : 0}% selesai</small></div>
                </div>)}</div> : <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="Tidak ada audit aktif yang perlu perhatian bulan ini" />}
            </Card>

            <Card className="dashboard-panel insights-panel" title="Saran dan perhatian" extra={<span className="panel-caption">Otomatis</span>}>
                <div className="insights-list">{insights.map((item, index) => <Alert key={index} type={item.type} showIcon message={item.title} description={item.detail} />)}</div>
                {monthlyCounts.total > 0 && <div className="closure-progress"><div><span>Rasio penyelesaian</span><b>{Math.round((monthlyCounts.closed / monthlyCounts.total) * 100)}%</b></div><Progress percent={Math.round((monthlyCounts.closed / monthlyCounts.total) * 100)} showInfo={false} strokeColor="#32a875" /></div>}
            </Card>
        </div>

        <div className="grid xl:grid-cols-3 gap-5">
            <Card className="dashboard-panel xl:col-span-2" title="Tren target 6 bulan" extra={<span className="panel-caption">Jumlah temuan</span>}>
                <ResponsiveContainer width="100%" height={250}><LineChart data={months} margin={{ top: 8, right: 12, left: -18, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" stroke="#edf0f5" vertical={false} /><XAxis dataKey="label" tick={{ fill: '#77849a', fontSize: 11 }} axisLine={false} tickLine={false} /><YAxis allowDecimals={false} tick={{ fill: '#98a2b3', fontSize: 10 }} axisLine={false} tickLine={false} /><Tooltip />
                    <Line type="monotone" dataKey="due" name="Target" stroke="#4e75ed" strokeWidth={2.5} dot={{ r: 3 }} /><Line type="monotone" dataKey="overdue" name="Overdue" stroke="#e45f63" strokeWidth={2.5} dot={{ r: 3 }} />
                </LineChart></ResponsiveContainer>
            </Card>
            <Card className="dashboard-panel attention-panel" title="Perlu ditindaklanjuti" extra={<Link href="/findings">Lihat semua</Link>}>
                {due.length ? due.map(finding => <Link className="attention-item" href={`/findings/${finding.id}`} key={finding.id}>
                    <span className={`attention-dot ${finding.computed_status}`} />
                    <span className="attention-main"><b>{finding.no_lha} · {finding.audit?.name || 'Audit'}</b><small>{finding.pic_name || finding.pic?.name || 'PIC belum ditentukan'} · Target {finding.target_date}</small></span>
                    {statusTag[finding.computed_status] || statusTag.open}
                </Link>) : <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="Tidak ada temuan yang perlu perhatian" />}
            </Card>
        </div>
    </AppLayout>;
}
