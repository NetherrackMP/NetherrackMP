import {buildPhar} from "../utils.js";

export const name = "Build phar";

export default async function () {
	await buildPhar();
};