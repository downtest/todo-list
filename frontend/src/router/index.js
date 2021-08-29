import {createRouter, createWebHistory} from "vue-router";
import Collections from "../components/Collections";
import User from "../components/User";
import MonthLayout from "../components/Calendar/Month/MonthLayout";
import Day from "../components/Calendar/Day"
import List from "../components/List";
import Registration from "../components/Registration";

const NotFound = { template: '<div>404</div>' }

const routes = [
    {path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
    {path: '/', component: List, meta: {title: 'Main page'}},
    {path: '/user', component: User, meta: {title: 'User'}},
    {path: '/registration', component: Registration, meta: {title: 'Registration'}},
    {path: '/calendar/month/:month?', name: 'calendarMonth', component: MonthLayout, meta: {title: 'Calendar'}, props: (route) => ({ month: /^\d{4}\-\d{2}$/.test(route.params.month) ? route.params.month : undefined }) },
    {path: '/calendar/day/:day', name: 'calendarDay', component: Day, meta: {title: 'Day'}, props: (route) => ({ day: /^\d{4}\-\d{2}\-\d{2}$/.test(route.params.day) ? route.params.day : undefined }) },
    {path: '/collections', component: Collections, meta: {title: 'Collections'} },
    {path: '/list/:parentId?', name: 'task-list', component: List, meta: {title: 'List'}, props: true },
]

const router = createRouter({
    history: createWebHistory(),
    linkActiveClass: 'link__active',
    routes, // short for `routes: routes`
})

router.beforeEach((to, from) => {
    document.title =  to.meta.title;
})

export default router;
