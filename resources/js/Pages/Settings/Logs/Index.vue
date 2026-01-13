<script setup>
import { ref, onMounted } from "vue";

// UI
import {
    Table,
    TableHeader,
    TableHead,
    TableRow,
    TableBody,
    TableCell,
} from "@/Components/ui/table";
import { Card, CardHeader, CardTitle, CardContent } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";

/* --------------------------------------------------------------------------
| State
-------------------------------------------------------------------------- */
const logs = ref([]);
const loading = ref(false);
const search = ref("");

/* --------------------------------------------------------------------------
| Fetch logs
-------------------------------------------------------------------------- */
const fetchLogs = async () => {
    loading.value = true;

    try {
        const res = await fetch("/settings/logs/list");
        logs.value = await res.json();
    } finally {
        loading.value = false;
    }
};

onMounted(fetchLogs);

/* --------------------------------------------------------------------------
| Computed
-------------------------------------------------------------------------- */
const filteredLogs = () => {
    if (!search.value) return logs.value;

    const q = search.value.toLowerCase();

    return logs.value.filter((log) =>
        Object.values(log).some((val) => String(val).toLowerCase().includes(q))
    );
};
</script>

<template>
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Configurações · Logs</h1>

            <Button variant="outline" @click="fetchLogs"> Atualizar </Button>
        </div>

        <!-- Logs table -->
        <Card>
            <CardHeader class="space-y-3">
                <CardTitle>Registos de Atividade</CardTitle>

                <Input
                    v-model="search"
                    placeholder="Pesquisar por utilizador, ação, IP…"
                />
            </CardHeader>

            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Data</TableHead>
                            <TableHead>Hora</TableHead>
                            <TableHead>Utilizador</TableHead>
                            <TableHead>Ação</TableHead>
                            <TableHead>IP</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-if="loading">
                            <TableCell colspan="5" class="text-center py-6">
                                A carregar logs…
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-for="(log, index) in filteredLogs()"
                            :key="index"
                        >
                            <TableCell>{{ log.date }}</TableCell>
                            <TableCell>{{ log.time }}</TableCell>
                            <TableCell class="font-medium">
                                {{ log.user }}
                            </TableCell>
                            <TableCell>{{ log.action }}</TableCell>
                            <TableCell>
                                {{ log.ip ?? "-" }}
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-if="!loading && filteredLogs().length === 0"
                        >
                            <TableCell colspan="5" class="text-center py-6">
                                Nenhum registo encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>
