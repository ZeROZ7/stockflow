export type Role = 'manager' | 'staff';

export interface User {
    id: number;
    name: string;
    email: string;
    role: Role;
    is_manager: boolean;
}

export interface Branch {
    id: number;
    code: string;
    name: string;
    city: string;
    address: string | null;
}

export interface Product {
    id: number;
    sku: string;
    name: string;
    unit: string;
    min_stock: number;
}

export interface StockRow {
    product_id: number;
    sku: string;
    name: string;
    unit: string;
    min_stock: number;
    quantity: number;
    is_low: boolean;
}

export interface LowStockItem {
    branch_id: number;
    branch_name: string;
    product_id: number;
    sku: string;
    product_name: string;
    unit: string;
    quantity: number;
    min_stock: number;
    shortage: number;
}

export type PurchaseOrderStatus = 'draft' | 'approved' | 'received';

export interface PurchaseOrderLine {
    id: number;
    product?: Product;
    quantity: number;
    unit_cost: number;
    subtotal: number;
}

export interface PurchaseOrder {
    id: number;
    reference: string;
    status: PurchaseOrderStatus;
    status_label: string;
    supplier: string;
    notes: string | null;
    branch?: Branch;
    lines?: PurchaseOrderLine[];
    total?: number;
    created_by?: string;
    approved_by?: string | null;
    received_by?: string | null;
    created_at: string | null;
    approved_at: string | null;
    received_at: string | null;
    can: { approve: boolean; receive: boolean };
}

export interface Dashboard {
    stats: {
        branches: number;
        products: number;
        open_purchase_orders: number;
        low_stock_items: number;
    };
    low_stock: LowStockItem[];
    recent_purchase_orders: PurchaseOrder[];
}

export interface Paginated<T> {
    data: T[];
    meta: { current_page: number; last_page: number; total: number };
}

export type ValidationErrors = Record<string, string[]>;
