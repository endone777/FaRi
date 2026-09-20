export type Product = {
    id: number;
    slug: string;
    brand: string;
    model: string;
    year_from: number;
    year_to: number;
    name: string;
    title: string;
    tech: string;
    color_temp: number;
    oem: string;
    shape: string;
    price: number;
    qty: number;
    in_stock: boolean;
    stock_note: string | null;
    weight: number;
    warranty_months: number;
    photo: string | null;
    photo_old: string | null;
    description: string | null;
    is_active: boolean;
};

export type DeliveryMethod = {
    id: number;
    code: string;
    name: string;
    cost: number;
    days: string;
    note: string | null;
    free_from: number | null;
    is_active: boolean;
    position: number;
};

export type CartLine = {
    key: string;
    side: string;
    qty: number;
    line_total: number;
    product: Product;
};

export type CartState = {
    lines: CartLine[];
    count: number;
    items_total: number;
    weight: number;
};

export type OrderItem = {
    id: number;
    title: string;
    oem: string | null;
    side: string;
    side_label: string;
    unit_price: number;
    qty: number;
    line_total: number;
};

export type Order = {
    id: number;
    number: string;
    customer_name: string;
    phone: string;
    email: string;
    vin: string | null;
    city: string | null;
    comment: string | null;
    delivery_name: string | null;
    delivery_cost: number;
    items_total: number;
    total: number;
    status: string;
    status_label: string;
    admin_note: string | null;
    created_at: string | null;
    items?: OrderItem[];
};

export type PageLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PageLink[];
    meta?: {
        current_page: number;
        last_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    current_page?: number;
    last_page?: number;
    total?: number;
};
