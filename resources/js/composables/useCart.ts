import { router, usePage } from '@inertiajs/vue3';
import { computed, type ComputedRef } from 'vue';
import cartRoutes from '@/routes/cart';
import type { CartLine, CartState, Product } from '@/types/fari';

const EMPTY: CartState = {
    lines: [],
    count: 0,
    items_total: 0,
    weight: 0,
};

export type UseCartReturn = {
    cart: ComputedRef<CartState>;
    lineFor: (product: Product) => CartLine | undefined;
    qtyOf: (product: Product) => number;
    add: (product: Product, qty?: number) => void;
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

    function lineFor(product: Product): CartLine | undefined {
        return cart.value.lines.find((line) => line.product.id === product.id);
    }

    function qtyOf(product: Product): number {
        return lineFor(product)?.qty ?? 0;
    }

    function add(product: Product, qty = 1): void {
        router.post(
            cartRoutes.store.url(),
            { product_id: product.id, qty },
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

    return { cart, lineFor, qtyOf, add, setQty, remove, clear };
}
