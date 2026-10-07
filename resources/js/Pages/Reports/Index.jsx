import React from 'react';
import { Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

const months = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

export default function Report({ rows, summary, filters, years }) {
    const selectedMonths = Array.isArray(filters.months)
        ? filters.months.map(String)
        : (filters.months ? [String(filters.months)] : []);
    const exportParams = new URLSearchParams();

    if (filters.year) exportParams.set('year', filters.year);
    if (filters.status) exportParams.set('status', filters.status);
    if (filters.rating) exportParams.set('rating', filters.rating);
    selectedMonths.forEach(month => exportParams.append('months[]', month));

    const exportQuery = exportParams.toString();
    const exportHref = `/findings-export${exportQuery ? `?${exportQuery}` : ''}`;

    return <AppLayout title="Report Monitoring">
        <form action="/reports" method="get" className="card mb-6 grid md:grid-cols-4 gap-3">
            <div>
                <label>Tahun</label>
                <select name="year" className="input" defaultValue={filters.year || ''}>
                    <option value="">Semua tahun</option>
                    {years.map(year => <option key={year} value={year}>{year}</option>)}
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status" className="input" defaultValue={filters.status || ''}>
                    <option value="">Semua</option>
                    <option value="open">open</option>
                    <option value="overdue">overdue</option>
                    <option value="closed">closed</option>
                </select>
            </div>
            <div>
                <label>Rating</label>
                <select name="rating" className="input" defaultValue={filters.rating || ''}>
                    <option value="">Semua</option>
                    <option>HIGH</option>
                    <option>MEDIUM</option>
                    <option>LOW</option>
                </select>
            </div>
            <div>
                <label>Bulan</label>
                <select multiple name="months[]" className="input h-28" defaultValue={selectedMonths}>
                    {months.map(month => <option value={month} key={month}>{new Date(2000, Number(month) - 1).toLocaleString('id-ID', { month: 'long' })}</option>)}
                </select>
            </div>
            <button className="btn-primary md:col-span-4">Tampilkan Report</button>
        </form>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            {Object.entries(summary).map(([label, value]) => <div className="card" key={label}>
                <div className="text-slate-500 uppercase text-xs">{label}</div>
                <div className="text-2xl font-bold">{value}</div>
            </div>)}
        </div>

        <div className="card overflow-x-auto">
            <div className="flex justify-between mb-4">
                <h2 className="font-bold">Detail Report</h2>
                <a className="btn" href={exportHref}>Export CSV (bisa dibuka di Excel)</a>
            </div>
            <table className="w-full text-sm">
                <thead><tr className="border-b text-left"><th>No LHA</th><th>Judul Temuan</th><th>Audit</th><th>Tahun</th><th>Rating</th><th>Unit</th><th>PIC</th><th>Target</th><th>Status</th></tr></thead>
                <tbody>{rows.map(finding => <tr className="border-b" key={finding.id}>
                    <td className="py-2"><Link className="text-blue-600" href={`/findings/${finding.id}`}>{finding.no_lha}</Link></td>
                    <td>{finding.judul_temuan || '-'}</td>
                    <td>{finding.audit?.name}</td>
                    <td>{finding.audit?.year}</td>
                    <td>{finding.rating}</td>
                    <td>{finding.unit?.name || '-'}</td>
                    <td>{finding.pic_name || finding.pic?.name || '-'}</td>
                    <td>{finding.target_date}</td>
                    <td>{finding.computed_status}</td>
                </tr>)}</tbody>
            </table>
        </div>
    </AppLayout>;
}
