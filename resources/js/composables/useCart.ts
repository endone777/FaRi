import { router, usePage } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';
import cartRoutes from '@/routes/cart';
import type { CartState, Product } from '@/types/fari';

const EMPTY: CartState = {
    lines: [],
    count: 0,
    items_total: 0,
    weight: 0,
};

export type UseCartReturn = {
    cart: ComputedRef<CartState>;
    add: (product: Product, side?: string, qty?: number) => void;
    setQty: (key: string, qty: number) => void;
    remove: (key: string) => void;
    clear: () => void;
};

/** The cart lives in the session and is shared with every Inertia page. */
export function useCart(): UseCartReturn {
    const page = usePage();

    const cart = computed<CartState>(
        () => (page.props.cart as CartState | undefined) ?? EMPTY,
    );

    function add(product: Product, side = 'left', qty = 1): void {
        router.post(
            cartRoutes.store.url(),
            { product_id: product.id, side, qty },
            { preserveScroll: true, preserveState: true },
        );
    }

    function setQty(key: string, qty: number): void {
        router.patch(
            cartRoutes.update.url(),
            { key, qty },
            { preserveScroll: true, preserveState: true },
        );
    }

    function remove(key: string): void {
        router.delete(cartRoutes.destroy.url(key), {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function clear(): void {
        router.delete(cartRoutes.clear.url(), {
            preserveScroll: true,
            preserveState: true,
        });
    }

    return { cart, add, setQty, remove, clear };
}
