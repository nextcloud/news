// src/types/nextcloud__router.d.ts
declare module '@nextcloud/router' {
    export function generateUrl(route: string): string;
    export function generateOcsUrl(route: string): string;
}
