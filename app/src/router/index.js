import Vue from "vue";
import VueRouter from "vue-router";
import MainPage from "../components/MainPage";
import User from "../components/User";
import Counter from "../components/Counter";
import Calendar from "../components/Calendar";
import List from "../components/List";
import Task from "../components/Task";

Vue.use(VueRouter);

const NotFound = { template: '<div>404</div>' }

const routes = [
    { path: '*', component: NotFound },
    { path: '/', component: MainPage, meta: {title: 'Main page'}},
    { path: '/user', component: User, meta: {title: 'User'}},
    { path: '/counter', component: Counter, meta: {title: 'Count me'} },
    { path: '/calendar', component: Calendar, meta: {title: 'Calendar'} },
    { path: '/list/:parentId?', name: 'task-list', component: List, props: true, meta: {title: 'List'} },
    { path: '/task/:id', name: 'task-detail', component: Task, props: true, meta: {title: 'Task'} },
]

const router = new VueRouter({
    mode: 'history',
    linkActiveClass: 'link__active',
    routes, // short for `routes: routes`
})

router.beforeEach((to, from, next) => {
    document.title =  to.meta.title;
    next()
})


export default router;
