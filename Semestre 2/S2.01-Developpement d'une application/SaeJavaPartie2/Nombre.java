public class Nombre extends Expression {

    // variable d'instance
    private int valeurNombre ;

    //Constructeur champ à champ
    public Nombre(int nombre)
    {
        this.valeurNombre = nombre;
    }

    // retourne un int represenant la valeur du Nombre
    public int valeur(){
        return this.valeurNombre;
    }

    // retourne un String representant la valeur
    public String toString() {
        return "" + this.valeurNombre;
    }
    
}
