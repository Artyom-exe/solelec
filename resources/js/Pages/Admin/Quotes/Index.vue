<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
</script>

<template>
    <AdminLayout>
        <section class="flex py-28 px-16 items-start gap-20 bg-[#2D2D2D]">
            <div
                class="flex max-w-[768px] flex-col items-start gap-4 text-white"
            >
                <h2 class="text-center font-inter text-base font-semibold">
                    Devis
                </h2>
                <div class="relative flex flex-col gap-6">
                    <h3
                        class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px] mb-6"
                    >
                        Mes demande de devis
                    </h3>
                    <div
                        class="absolute bottom-0 right-1/3 border-2 border-[#FF8C42] w-[80%] max-w-[353px] min-w-[200px]"
                    ></div>
                </div>
            </div>
            <article class="flex flex-col items-start gap-12 flex-1">
                <div class="flex items-center">
                    <button
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Voir tout
                    </button>
                    <button
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Types
                    </button>
                    <button
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Clients
                    </button>
                </div>
            </article>
        </section>
    </AdminLayout>
</template>
