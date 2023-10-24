package fr.einfolearning.tp2.utilitaires;
import fr.einfolearning.tp2.metiers.TextEditor;

/**
 * Classe utilitaire pour l'analyse du fonctionnement de l'editeur de texte
 * @author B. LEMAIRE
 * @version 2023
 */
public class EmacsAnalyze {

    /**
     * Méthode utilitaire permettant d'analyser le contenu du buffer de l'editeur de texte
     * avec la position des différents curseurs
     * @param ed (TextEditeur)
     * @param insIndex (boolean) : true affiche l'index des caractères, sinon pas d'affichage
     */
    public static void dumpTextEditor(TextEditor ed, boolean insIndex) {
        int yankLeft;
        int cursor;
        int i;
        String stringInEdBuffer;
        int yankRight;
        int mark;
        cursor = ed.getCursor();
        yankLeft = ed.getYankLeft();
        yankRight = ed.getYankRight();
        mark= ed.getMark();

        stringInEdBuffer = ed.getTextBuffer().toString();
        i=0;
        for (char c : stringInEdBuffer.toCharArray()){
            String marqueurs="[";
            if (i==cursor) marqueurs+= "C";
            if (i==mark) marqueurs+="M";
            if (i==yankLeft) marqueurs+="*YL*>>>";
            if (i==yankRight) marqueurs+="<<<*YR*";

            marqueurs+="]";
            if (marqueurs.length()>2)  System.out.print(marqueurs);

            System.out.print(c+"");



            if (insIndex) System.out.print("("+ i +") ");
            i = i + 1;

        }
        System.out.println();
    }
}
