import React from 'react';
import { useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function Form({ finding, audits, units }) {
    const edit = !!finding;
    const initialItems = finding?.items?.length
        ? finding.items.map(item => item.description)
        : (finding?.temuan ? [finding.temuan] : ['']);
    const form = useForm({
        audit_id: finding?.audit_id || audits[0]?.id || '',
        unit_id: finding?.unit_id || '',
        no_lha: finding?.no_lha || '',
        judul_temuan: finding?.judul_temuan || '',
        inisial: finding?.inisial || '',
        rating: finding?.rating || 'MEDIUM',
        temuan_items: initialItems,
        rekomendasi: finding?.rekomendasi || '',
        action_plan: finding?.action_plan || '',
        pic_name: finding?.pic_name || finding?.pic?.name || '',
        target_date: finding?.target_date || '',
        status: finding?.computed_status || 'open',
        closed_date: finding?.closed_date || '',
        progress: finding?.progress || 0,
        version: finding?.version || 1,
    });

    const setItem = (index, value) => {
        const items = [...form.data.temuan_items];
        items[index] = value;
        form.setData('temuan_items', items);
    };
    const submit = event => {
        event.preventDefault();
        edit ? form.put(`/findings/${finding.id}`) : form.post('/findings');
    };

    return <AppLayout title={edit ? 'Edit Temuan' : 'Tambah Temuan'}>
        <form onSubmit={submit} className="card grid md:grid-cols-2 gap-4">
            <div><label>Audit</label><select className="input" value={form.data.audit_id} onChange={event => form.setData('audit_id', event.target.value)} required>{audits.map(audit => <option value={audit.id} key={audit.id}>{audit.name} ({audit.year})</option>)}</select></div>
            <div><label>Unit Kerja</label><select className="input" value={form.data.unit_id} onChange={event => form.setData('unit_id', event.target.value)}><option value="">-</option>{units.map(unit => <option value={unit.id} key={unit.id}>{unit.name}</option>)}</select></div>
            <div><label>No LHA</label><input className="input" type="text" inputMode="numeric" pattern="[0-9]*" placeholder="1, 2, 3, ..." maxLength="20" value={form.data.no_lha} onChange={event => form.setData('no_lha', event.target.value.replace(/[^0-9]/g, ''))} required/><small className="text-slate-500">Masukkan nomor saja.</small>{form.errors.no_lha && <p className="text-red-600">{form.errors.no_lha}</p>}</div>
            <div><label>Judul Temuan</label><input className="input" maxLength="255" placeholder="Contoh: Pengelolaan akses belum memadai" value={form.data.judul_temuan} onChange={event => form.setData('judul_temuan', event.target.value)} required/>{form.errors.judul_temuan && <p className="text-red-600">{form.errors.judul_temuan}</p>}</div>
            <div><label>Inisial</label><input className="input" value={form.data.inisial} onChange={event => form.setData('inisial', event.target.value)}/></div>
            <div><label>Rating</label><select className="input" value={form.data.rating} onChange={event => form.setData('rating', event.target.value)}><option>HIGH</option><option>MEDIUM</option><option>LOW</option></select></div>
            <div><label>PIC</label><input className="input" value={form.data.pic_name} onChange={event => form.setData('pic_name', event.target.value)} placeholder="Nama PIC dari unit terkait" maxLength="255"/></div>
            <div><label>Target</label><input type="date" className="input" value={form.data.target_date} onChange={event => form.setData('target_date', event.target.value)} required/></div>
            <div><label>Progress (%)</label><input type="number" min="0" max="100" className="input" value={form.data.progress} onChange={event => form.setData('progress', event.target.value)}/></div>

            <div className="md:col-span-2 finding-items-field">
                <div className="finding-items-heading"><div><label>Rincian Temuan</label><small>Satu judul dapat memiliki beberapa rincian temuan.</small></div><button type="button" className="btn" onClick={() => form.setData('temuan_items', [...form.data.temuan_items, ''])}>+ Tambah Rincian</button></div>
                {form.data.temuan_items.map((item, index) => <div className="finding-item-input" key={index}>
                    <label htmlFor={`finding-item-${index}`}>Temuan {index + 1}</label>
                    <textarea id={`finding-item-${index}`} className="input min-h-24" value={item} onChange={event => setItem(index, event.target.value)} required/>
                    {form.errors[`temuan_items.${index}`] && <p className="text-red-600">{form.errors[`temuan_items.${index}`]}</p>}
                    {form.data.temuan_items.length > 1 && <button type="button" className="remove-finding-item" onClick={() => form.setData('temuan_items', form.data.temuan_items.filter((_, itemIndex) => itemIndex !== index))}>Hapus rincian</button>}
                </div>)}
                {form.errors.temuan_items && <p className="text-red-600">{form.errors.temuan_items}</p>}
            </div>
            <div className="md:col-span-2"><label>Rekomendasi</label><textarea className="input min-h-24" value={form.data.rekomendasi} onChange={event => form.setData('rekomendasi', event.target.value)}/></div>
            <div className="md:col-span-2"><label>Action Plan Unit Kerja</label><textarea className="input min-h-24" value={form.data.action_plan} onChange={event => form.setData('action_plan', event.target.value)}/></div>
            {edit && <div><label>Status</label><select className="input" value={form.data.status} onChange={event => form.setData('status', event.target.value)}><option value="open">open</option><option value="overdue">overdue</option><option value="closed">closed</option></select></div>}
            <div className="md:col-span-2"><button className="btn-primary" disabled={form.processing}>{form.processing ? 'Menyimpan…' : 'Simpan'}</button></div>
        </form>
    </AppLayout>;
}
