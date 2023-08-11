import {runSetup} from "../utils.js";
import fs from "fs";
import {join} from "path";

export const name = "Install composer";

export default async function () {
	const composerPharPath = join(DIR, "../composer.phar");
	if (!fs.existsSync(composerPharPath)) {
		await runSetup();
	} else if (!fs.statSync(composerPharPath).isFile()) throw "Expected composer.phar to be a file!";
};