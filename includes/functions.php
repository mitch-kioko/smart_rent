<?php
// Rent months due: every calendar month from the start month through the
// end month (or the current month if the tenancy is ongoing).
function months_due($start_date, $end_date = null) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date ?? 'today');
    $months = ($end->format('Y') - $start->format('Y')) * 12
            + ($end->format('n') - $start->format('n')) + 1;
    return max(0, $months);
}

// Label and badge style for a tenancy row
function overdue_info($r) {
    if ($r['status'] === 'Ended') {
        return $r['balance'] > 0
            ? ['Left owing', 'badge-bad']
            : ['Ended', 'badge-muted'];
    }
    if ($r['balance'] > 0) {
        $m = $r['months_behind'];
        $label = 'Overdue (' . $m . ' month' . ($m > 1 ? 's' : '') . ')';
        return [$label, $m >= 2 ? 'badge-bad' : 'badge-warn'];
    }
    return $r['balance'] < 0 ? ['Paid in advance', 'badge-ok'] : ['Paid up', 'badge-ok'];
}

// Every tenancy with tenant, unit, totals, balance and overdue details
function get_tenancy_balances($pdo) {
    $sql = "SELECT t.tenancy_id, t.tenant_id, t.rent_per_month, t.start_date, t.end_date, t.status,
                   u.first_name, u.last_name, u.phone_number, un.unit_number,
                   COALESCE((SELECT SUM(p.amount) FROM payment p
                             WHERE p.tenancy_id = t.tenancy_id), 0) AS total_paid
            FROM tenancies t
            JOIN users u ON u.user_id = t.tenant_id
            JOIN units un ON un.unit_id = t.unit_id";
    $rows = $pdo->query($sql)->fetchAll();

    foreach ($rows as &$r) {
        $r['months_due'] = months_due($r['start_date'], $r['end_date']);
        $r['rent_due'] = $r['months_due'] * $r['rent_per_month'];
        $r['balance'] = $r['rent_due'] - $r['total_paid'];
        $r['months_behind'] = ($r['balance'] > 0 && $r['rent_per_month'] > 0)
            ? (int)ceil(round($r['balance'] / $r['rent_per_month'], 4))
            : 0;
        [$r['flag_label'], $r['flag_class']] = overdue_info($r);
    }
    unset($r);
    return $rows;
}